<?php

/**
 * RelayManagerPM-Discord-Relay
 *
 * Copyright (C) 2018 Jack Noordhuis
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author Jack
 *
 */

declare(strict_types=1);

namespace jacknoordhuis\discordrelay\connection;

use CharlotteDunois\Yasmin\Client as DiscordClient;
use CharlotteDunois\Yasmin\Interfaces\TextChannelInterface;
use CharlotteDunois\Yasmin\Models\Message;
use CharlotteDunois\Yasmin\Models\MessageEmbed;
use CharlotteDunois\Yasmin\Models\TextChannel;
use CharlotteDunois\Yasmin\Utils\URLHelpers;
use jacknoordhuis\discordrelay\models\RelayChannel;
use jacknoordhuis\discordrelay\models\RelayMessage;
use jacknoordhuis\discordrelay\models\RelayOptions;
use jacknoordhuis\discordrelay\connection\utils\RelayLoggerAttachment;
use jacknoordhuis\discordrelay\utils\DiscordTextFormat;
use pocketmine\utils\MainLogger;
use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;

class RelayManager {

	/** @var RelayThread */
	private $thread;

	/** @var MainLogger */
	private $logger;

	/** @var RelayOptions */
	private $options;

	/** @var LoopInterface */
	private $loop;

	/** @var DiscordClient */
	private $client;

	/** @var RelayLoggerAttachment|null */
	private $loggerAttachment = null;

	/** @var RelayChannel[] */
	private $discordRelayChannels = [];

	/** @var RelayChannel[] */
	private $consoleRelayChannels = [];

	public function __construct(RelayThread $thread) {
		$this->thread = $thread;
		$this->logger = $thread->getLogger();
		$this->options = $this->thread->getOptions();
		$this->loop = Factory::create();

		URLHelpers::setLoop($this->loop);

		// setup the manager
		$this->setup();

		// loop until shutdown
		$this->loop->run();
	}

	/**
	 * @return \AttachableThreadedLogger
	 */
	public function logger() : \AttachableThreadedLogger {
		return $this->logger;
	}

	/**
	 * @return RelayOptions
	 */
	public function options() : RelayOptions {
		return $this->options;
	}

	/**
	 * All the logic for managing the loop, creating the client, and registering callbacks.
	 */
	protected function setup() : void {
		// add a repeating callback to the loop to check if the thread is shutdown
		$this->loop->addPeriodicTimer(1, function() {
			if($this->thread->isShutdown()) {
				$this->cleanup();
			}
		});

		// add arepeating callback to the loop to check for outgoing messages from the main thread
		$this->loop->addPeriodicTimer(1, function() {
			$messages = [];
			while(($serialized = $this->thread->nextOutboundMessage()) !== null) {
				$message = new RelayMessage();
				$message->fastUnserialize($serialized, $this->options);

				$messages[$message->channel()->id()][] = $message;
			}

			foreach($messages as $channelId => $channelMessages) {
				$channel = $this->options->channel((string) $channelId); // why you cast to int php???
				$content = "";

				foreach($channelMessages as $message) {
					/** @var $message RelayMessage */
					$content .= $message->author() . ": " . $message->content() . "\n";
				}

				if($channel->hasFlag(RelayChannel::FLAG_EMBED_MESSAGES)) {
					$embed = new MessageEmbed();
					$embed
						->addField("Messages from #" . $channel->alias(), $content)
						->setColor($channel->embedColor())
						->setTimestamp();

					$this->client->channels->get($channelId)->send("", ["embed" => $embed]);
				} else {
					$this->client->channels->get($channelId)->send($content);
				}
			}
		});

		// create the client instance
		$this->client = new DiscordClient([], $this->loop);

		// register the on ready callback
		$this->client->on("ready", function() {
			$this->ready();
		});

		// register the message callback
		$this->client->on("message", function($message) {
			$this->message($message);
		});

		// register the error callback
		$this->client->on("error", function($error) {
			$this->error($error);
		});

		// create the client, create the gateway connection and login to discord
		$this->client->login($this->options->token())->done();
	}

	/**
	 * All logic to execute when the client is ready.
	 */
	public function ready() : void {
		// warn of channels that can't be found or aren't text channels
		foreach($this->options->channels() as $channel) {
			try {
				if(!($c = $this->client->channels->resolve($channel->id())) instanceof TextChannel) {
					$this->logger->warning("Unable to setup bridge for non-text channel #{$channel->id()}.");
				}

				// check if we should relay incoming messages from this channel
				if($channel->hasFlag(RelayChannel::FLAG_RELAY_FROM_DISCORD)) {
					$this->discordRelayChannels[$c->id] = $channel; // index the channels by the discord channel id
				}

				// check if we should relay console messages to this channel
				if(!empty($channel->consoleLogLevels())) {
					$this->consoleRelayChannels[$c->id] = $channel; // index the channels by the discord channel id
					// add the attachment to the logger at the first console relay channel
					if($this->loggerAttachment === null) {
						$this->logger()->addAttachment($this->loggerAttachment = new RelayLoggerAttachment());
					}
				}
			} catch(\InvalidArgumentException $e) {
				$this->logger->warning("Unable to find channel #{$channel->id()}, a bridge will not be setup for this channel.");
			}
		}

		// only register the timer if the logger attachment was created
		if($this->loggerAttachment !== null) {
			// add a repeating callback to the loop to relay console messages
			$this->loop->addPeriodicTimer(1, function() {
				$this->relayConsoleMessages();
			});
		}

		$this->client->user->setStatus("online");
		$this->client->user->setGame("PocketMine Bridge");

		$this->logger()->debug("Logged in as " . $this->client->user->tag . " created on " . $this->client->user->createdAt->format("d.m.Y H:i:s"));
	}

	/**
	 * All logic to execute when the client receives a message.
	 *
	 * @param Message $message
	 */
	public function message(Message $message) : void {
		if(
			$message->author->id !== $this->client->user->id and // don't relay messages sent by the bot
			isset($this->discordRelayChannels[$id = $message->channel->id]) // only relay messages for channels it is enabled for
		) {
			$relay = new RelayMessage();
			$relay->setChannel($this->discordRelayChannels[$id]);
			$relay->setAuthor($message->author->username);
			$relay->setContent($message->content);
			$this->thread->pushInboundMessage($relay->serialize(true));
		}
	}

	/**
	 * All logic to execute when the client encounters an error.
	 *
	 * @param \Exception $error
	 */
	public function error(\Exception $error) : void {
		$this->thread->handleException($error);
	}

	/**
	 * All logic to relay the console output to discord.
	 */
	public function relayConsoleMessages() : void {
		$messages = [];
		/** @var string|null $lastLevel */
		$lastLevel = null;

		while(($log = $this->loggerAttachment->getOutboundMessage()) !== null) {
			$log = unserialize($log);
			$level = $log["level"];

			if($lastLevel !== $level and $lastLevel !== null) {
				$this->relayMessagesToConsole($messages, $lastLevel);
				$messages = [];
			}

			$messages[] = $log["message"];
			$lastLevel = $level;
		}

		if(!empty($messages)) {
			$this->relayMessagesToConsole($messages, $lastLevel);
		}
	}

	/**
	 * @param string[] $messages
	 * @param string $level
	 */
	protected function relayMessagesToConsole(array $messages, string $level) : void {
		$content = implode("\n", $messages);

		$embed = new MessageEmbed();
		$embed
			->setDescription($content);

		foreach($this->consoleRelayChannels as $channel) {
			if($channel->hasConsoleLogLevel($level)) {
				$logLevel = $channel->consoleLogLevel($level);
				if($logLevel->embed()) {
					$embed->setColor($logLevel->embedColor());
					$this->client->channels->get($channel->id())->send("", ["embed" => $embed]);
				} else {
					$this->client->channels->get($channel->id())->send($content);
				}
			}
		}
	}

	/**
	 * All logic to execute when the thread shuts down.
	 */
	public function cleanup() : void {
		if($this->loggerAttachment !== null) {
			$this->logger()->removeAttachment($this->loggerAttachment);
		}

		$this->client->user->setStatus("invisible")->done(function() {
			$this->loop->stop();
		});
	}

}
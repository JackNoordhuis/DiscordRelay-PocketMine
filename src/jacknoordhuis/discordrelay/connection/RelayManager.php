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
use jacknoordhuis\discordrelay\connection\models\RelayChannel;
use jacknoordhuis\discordrelay\connection\models\RelayOptions;
use jacknoordhuis\discordrelay\connection\utils\RelayLoggerAttachment;
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

	/** @var RelayLoggerAttachment */
	private $loggerAttachment;

	/** @var RelayChannel[]|null */
	private $consoleRelayChannels = null;

	public function __construct(RelayThread $thread) {
		$this->thread = $thread;
		$this->logger = $thread->getLogger();
		$this->options = $this->thread->getOptions();
		$this->loop = Factory::create();

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

		// loop through the channels to see if we need to relay console messages
		foreach($this->options->channels() as $channel) {
			if($channel->hasFlag(RelayChannel::FLAG_RELAY_CONSOLE)) {
				// add the attachment to the logger at the first console relay channel
				$this->logger()->addAttachment($this->loggerAttachment = new RelayLoggerAttachment());
				break;
			}
		}

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
		$this->logger()->debug("Logged in as " . $this->client->user->tag . " created on " . $this->client->user->createdAt->format("d.m.Y H:i:s"));

		// only register the timer if the logger attachment was created
		if($this->loggerAttachment !== null) {
			// add a repeating callback to the loop to relay console messages
			$this->loop->addPeriodicTimer(1, function() {
				$this->relayConsoleMessages();
			});
		}

		$this->client->user->setStatus("online");
		$this->client->user->setGame("PocketMine Bridge");
	}

	/**
	 * All logic to execute when the client receives a message.
	 *
	 * @param Message $message
	 */
	public function message(Message $message) : void {
		if(($relayChannel = $this->options->channel($message->channel->id)) !== null) {
			if($relayChannel->hasFlag(RelayChannel::FLAG_RELAY_FROM_DISCORD)) {
				$this->logger()->info("[DiscordRelay] #{$relayChannel->alias()} | {$message->author->username}: {$message->content}");
			}
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
		if($this->consoleRelayChannels === null) {
			$this->consoleRelayChannels = [];
			foreach($this->options->channels() as $channel) {
				if($channel->hasFlag(RelayChannel::FLAG_RELAY_CONSOLE)) {
					$this->consoleRelayChannels[] = $channel;
				}
			}
		}

		while($message = $this->loggerAttachment->getOutboundMessage()) {
			foreach($this->consoleRelayChannels as $channel) {
				/** @var TextChannelInterface $discordChannel */
				$discordChannel = $this->client->channels->get($channel->id());
				$discordChannel->send($message);
			}
		}
	}

	/**
	 * All logic to execute when the thread shuts down.
	 */
	public function cleanup() : void {
		$this->logger()->removeAttachment($this->loggerAttachment);
		$this->client->user->setStatus("invisible")->done(function() {
			$this->loop->stop();
		});
	}

}
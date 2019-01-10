<?php

/**
 * BotConfigurationLoader.phpM-Discord-Relay
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

namespace jacknoordhuis\discordrelay\utils\config;

use jacknoordhuis\discordrelay\DiscordRelay;
use jacknoordhuis\discordrelay\models\RelayChannel;
use jacknoordhuis\discordrelay\models\RelayOptions;

class BotConfigurationLoader extends ConfigurationLoader {

	public function onLoad(array $data) : void {
		$general = $data["general"];
		$options = new RelayOptions();

		$options->setToken($general["bot"]["token"]);

		$this->loadChannels($options, $general["channels"]);
		$this->loadDefaultChannel($options, $general["default-channel"]);

		$this->getPlugin()->setRelayOptions($options);
	}

	protected function loadChannels(RelayOptions $options, array $channels) : void {
		foreach($channels as $data) {
			$options->addChannel($this->loadChannel($data));
		}
	}

	protected function loadDefaultChannel(RelayOptions $options, string $defaultChannelAlias) : void {
		foreach($options->channels() as $channel) {
			if($channel->alias() === $defaultChannelAlias) {
				$options->setDefaultChannel($channel->id());
				break;
			}
		}
	}

	protected function loadChannel(array $data) : RelayChannel {
		$channel = new RelayChannel();

		$channel->setAlias($data["name"]);
		$channel->setId((string) $data["discord-id"]);
		$channel->setEmbedColor($data["embed-color"] ?? DiscordRelay::DEFAULT_EMBED_COLOR);

		$opts = $data["options"];
		if(($relayFrom = $opts["relay-from-discord"]?? false) and self::getBoolean($relayFrom)) {
			$channel->setFlag(RelayChannel::FLAG_RELAY_FROM_DISCORD);
		}

		if(($relayTo = $opts["relay-to-discord"] ?? false) and self::getBoolean($relayTo)) {
			$channel->setFlag(RelayChannel::FLAG_RELAY_TO_DISCORD);
		}

		if(($relayConsole = $opts["relay-console"] ?? false) and self::getBoolean($relayConsole)) {
			$channel->setFlag(RelayChannel::FLAG_RELAY_CONSOLE);
		}

		return $channel;
	}

}
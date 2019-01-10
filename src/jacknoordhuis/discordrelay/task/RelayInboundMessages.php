<?php

/**
 * RelayInboundMessages.php – PM-Discord-Relay
 *
 * Copyright (C) 2019 Jack Noordhuis
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

namespace jacknoordhuis\discordrelay\task;

use jacknoordhuis\discordrelay\models\RelayMessage;
use jacknoordhuis\discordrelay\DiscordRelay;
use pocketmine\scheduler\Task;

class RelayInboundMessages extends Task {

	/** @var DiscordRelay */
	private $plugin;

	/** @var int */
	private $relayedPerTick = 10; // maximum number of messages to relay per tick

	public function __construct(DiscordRelay $plugin) {
		$this->plugin = $plugin;
	}

	public function onRun(int $currentTick) {
		$relayed = 0;
		while(($serialized = $this->plugin->getRelayThread()->nextInboundMessage()) != null and $relayed < $this->relayedPerTick) {
			$message = new RelayMessage();
			$message->fastUnserialize($serialized, $this->plugin->getRelayOptions());

			$this->plugin->getServer()->broadcastMessage("[DiscordRelay] #{$message->channel()->alias()} | {$message->author()}: {$message->content()}");
			$relayed++;
		}
	}

}
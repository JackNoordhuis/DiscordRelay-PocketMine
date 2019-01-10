<?php

/**
 * CheckRelayThread.php – PM-Discord-Relay
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

use jacknoordhuis\discordrelay\DiscordRelay;
use pocketmine\scheduler\Task;

class CheckRelayThread extends Task {

	/** @var DiscordRelay */
	private $plugin;

	public function __construct(DiscordRelay $plugin) {
		$this->plugin = $plugin;
	}

	public function onRun(int $currentTick) {
		if(!$this->plugin->getRelayThread()->isRunning() and !$this->plugin->getRelayThread()->isShutdown()) {
			$this->plugin->getLogger()->logException(new \Exception("RelayThread crashed without crash information"));
			$this->plugin->getServer()->shutdown();
		}
	}

}
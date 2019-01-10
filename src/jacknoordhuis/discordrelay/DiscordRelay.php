<?php

/**
 * DiscordRelay.php – PM-Discord-Relay
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

namespace jacknoordhuis\discordrelay;

use jacknoordhuis\discordrelay\connection\RelayThread;
use jacknoordhuis\discordrelay\event\EventManager;
use jacknoordhuis\discordrelay\models\RelayOptions;
use jacknoordhuis\discordrelay\task\CheckRelayThread;
use jacknoordhuis\discordrelay\task\RelayInboundMessages;
use jacknoordhuis\discordrelay\utils\AutoloaderLoader;
use jacknoordhuis\discordrelay\utils\config\BotConfigurationLoader;
use pocketmine\plugin\PluginBase;
use pocketmine\snooze\SleeperNotifier;

class DiscordRelay extends PluginBase {

	/** @var SleeperNotifier */
	private $relayThreadSleeper;

	/** @var RelayThread */
	private $discordThread;

	/** @var EventManager */
	private $eventManager;

	/** @var RelayOptions */
	private $discordRelayOptions;

	/** @var CheckRelayThread */
	private $checkRelayThreadClass = null;
	/** @var RelayInboundMessages */
	private $relayInboundTask = null;

	/** @var BotConfigurationLoader */
	private $botConfigLoader;

	const SETTINGS_CONFIG = "Settings.yml";

	public function onLoad() {
		AutoloaderLoader::load(); // hack to trigger loading the composer autoload file
	}

	public function onEnable() {
		$this->saveResource(self::SETTINGS_CONFIG);

		$this->eventManager = new EventManager($this);

		$this->botConfigLoader = new BotConfigurationLoader($this, $this->getDataFolder() . self::SETTINGS_CONFIG);

		$this->relayThreadSleeper = new SleeperNotifier();
		$this->discordThread = new RelayThread($this->getServer()->getLogger(), $this->discordRelayOptions->serialize(), $this->relayThreadSleeper);
		$this->getScheduler()->scheduleRepeatingTask($this->checkRelayThreadClass = new CheckRelayThread($this), 1);

		$this->getScheduler()->scheduleRepeatingTask($this->relayInboundTask = new RelayInboundMessages($this), 20);
	}

	public function onDisable() {
		if($this->discordThread !== null) {
			$this->discordThread->shutdown();
		}

		$this->getScheduler()->cancelTask($this->relayInboundTask->getTaskId());
	}

	public function getRelayOptions() : RelayOptions {
		return $this->discordRelayOptions;
	}

	public function getRelayThread() : RelayThread {
		return $this->discordThread;
	}

	public function getEventManager() : EventManager {
		return $this->eventManager;
	}

	public function setRelayOptions(RelayOptions $options) : void {
		if($this->discordRelayOptions !== null) {
			return;
		}

		$this->discordRelayOptions = $options;
	}

}
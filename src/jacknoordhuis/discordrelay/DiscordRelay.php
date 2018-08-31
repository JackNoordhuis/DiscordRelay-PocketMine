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
use jacknoordhuis\discordrelay\connection\models\RelayOptions;
use jacknoordhuis\discordrelay\utils\AutoloaderLoader;
use jacknoordhuis\discordrelay\utils\config\BotConfigurationLoader;
use pocketmine\plugin\PluginBase;
use pocketmine\snooze\SleeperNotifier;

class DiscordRelay extends PluginBase {

	/** @var SleeperNotifier */
	private $relayThreadSleeper;

	/** @var RelayThread */
	private $discordThread;

	/** @var RelayOptions */
	private $discordRelayOptions;

	/** @var BotConfigurationLoader */
	private $botConfigLoader;

	const SETTINGS_CONFIG = "Settings.yml";

	public function onLoad() {
		AutoloaderLoader::load(); // hack to trigger loading the composer autoload file
	}

	public function onEnable() {
		$this->saveResource(self::SETTINGS_CONFIG);

		$this->botConfigLoader = new BotConfigurationLoader($this, $this->getDataFolder() . self::SETTINGS_CONFIG);

		$this->relayThreadSleeper = new SleeperNotifier();
		$this->discordThread = new RelayThread($this->getServer()->getLogger(), $this->discordRelayOptions->serialize(), $this->relayThreadSleeper);
	}

	public function onDisable() {
		if($this->discordThread !== null) {
			$this->discordThread->shutdown();
		}
	}

	public function getRelayOptions() : RelayOptions {
		return $this->discordRelayOptions;
	}

	public function setRelayOptions(RelayOptions $options) : void {
		if($this->discordRelayOptions !== null) {
			return;
		}

		$this->discordRelayOptions = $options;
	}

}
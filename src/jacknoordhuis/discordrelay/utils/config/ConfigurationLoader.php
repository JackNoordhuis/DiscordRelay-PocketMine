<?php

/**
 * ConfigurationLoader.php – PM-Discord-Relay
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
use pocketmine\utils\Config;

/**
 * Basic class to help manage configuration values
 */
abstract class ConfigurationLoader {

	/** @var DiscordRelay */
	private $plugin;

	/** @var string */
	private $path;

	/** @var array */
	private $data;

	public function __construct(DiscordRelay $plugin, string $path) {
		$this->plugin = $plugin;
		$this->path = $path;

		$this->loadData();

		$this->onLoad($this->data);
	}

	public function getPlugin() : DiscordRelay {
		return $this->plugin;
	}

	final public function loadData() {
		$this->data = (new Config($this->path))->getAll(); // use pocketmine config class to detect file type and parse into array
	}

	final public function saveData() {
		$config = new Config($this->path);
		$config->setAll($this->data);
		$config->save();
	}

	final public function reloadData() {
		$this->saveData();
		$this->loadData();
	}

	/**
	 * Called when the config is loaded
	 *
	 * @param array $data
	 */
	abstract protected function onLoad(array $data) : void;

	/**
	 * Retrieve a boolean value
	 *
	 * @param string|int $value
	 *
	 * @return bool
	 */
	public static function getBoolean($value) : bool {
		if(is_bool($value)) {
			return $value;
		}
		switch(is_string($value) ? strtolower($value) : $value) {
			case "off":
			case "false":
			case "no":
			case 0:
				return false;
		}
		return true;
	}

}
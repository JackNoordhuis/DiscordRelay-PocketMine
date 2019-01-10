<?php

/**
 * RelayChannelPM-Discord-Relay
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

namespace jacknoordhuis\discordrelay\models;

class RelayChannel implements \Serializable {

	/**
	 * Alias used to switch between channels in the server chat
	 *
	 * @var string
	 */
	private $serverAlias;

	/**
	 * The discord channel id
	 *
	 * @var string
	 */
	private $discordChannelId;

	/**
	 * The default color for embeds
	 *
	 * @var int
	 */
	private $embedColor;

	/**
	 * The flags set for this channel
	 *
	 * @var int
	 */
	private $flags = 0;

	/**
	 * Array of console levels to relay to this channel
	 *
	 * @var RelayLogLevel[]
	 */
	private $consoleLogLevels = [];

	public const FLAG_RELAY_FROM_DISCORD = 1; // listen for messages from discord and send them to clients on the server
	public const FLAG_RELAY_TO_DISCORD = 2; // listen for messages on the server and send them to discord
	public const FLAG_EMBED_MESSAGES = 3; // embed all normal messages (console log level configuration can override this)

	public function alias() : string {
		return $this->serverAlias;
	}

	public function setAlias(string $alias) : void {
		$this->serverAlias = $alias;
	}

	public function id() : string {
		return $this->discordChannelId;
	}

	public function setId(string $id) : void {
		if($this->discordChannelId !== null) {
			throw new \RuntimeException("Tried to change id of channel when it is already set!");
		}

		$this->discordChannelId = $id;
	}

	public function embedColor() : int {
		return $this->embedColor;
	}

	public function setEmbedColor(int $color) {
		if($color > 16777215) {
			throw new \InvalidArgumentException("Embed color must be less than 16777215");
		}

		$this->embedColor = $color;
	}

	public function flags() : int {
		return $this->flags;
	}

	public function hasFlag(int $flag) : bool {
		return ($this->flags & (1 << $flag)) > 0;
	}

	public function setFlag(int $flag, bool $value = true) : void {
		if($this->hasFlag($flag) !== $value) {
			$this->flags ^= 1 << $flag;
		}
	}

	/**
	 * @return RelayLogLevel[]
	 */
	public function consoleLogLevels() : array {
		return $this->consoleLogLevels;
	}

	public function consoleLogLevel(string $level) : ?RelayLogLevel {
		return $this->consoleLogLevels[$level] ?? null;
	}

	public function hasConsoleLogLevel(string $level) : bool {
		return isset($this->consoleLogLevels[$level]);
	}

	public function setConsoleLogLevel(RelayLogLevel $level) : void {
		$this->consoleLogLevels[$level->level()] = $level;
	}

	public function serialize() {
		return json_encode($this->toArray());
	}

	public function unserialize($serialized) {
		$this->fromArray(json_decode($serialized, true));
	}

	public function __toString() {
		return json_encode($this->toArray());
	}

	public function toArray() : array {
		return [
			"name" => $this->serverAlias,
			"id" => $this->discordChannelId,
			"embed_color" => $this->embedColor,
			"flags" => $this->flags,
			"log_levels" => array_map(function(RelayLogLevel $level) {
				return $level->toArray();
			}, $this->consoleLogLevels),
		];
	}

	public function fromArray(array $data) : void {
		$this->serverAlias = $data["name"];
		$this->discordChannelId = $data["id"];
		$this->embedColor = $data["embed_color"];
		$this->flags = $data["flags"];

		foreach($data["log_levels"] as $key => $level) {
			$this->consoleLogLevels[$key] = $l = new RelayLogLevel();
			$l->fromArray($level);
		}
	}

}
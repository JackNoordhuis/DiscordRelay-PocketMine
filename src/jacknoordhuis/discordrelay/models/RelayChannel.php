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
	 * The flags set for this channel
	 *
	 * @var int
	 */
	private $flags = 0;

	public const FLAG_RELAY_FROM_DISCORD = 1; // listen for messages from discord and send them to clients on the server
	public const FLAG_RELAY_TO_DISCORD = 2; // listen for messages on the server and send them to discord
	public const FLAG_RELAY_CONSOLE = 3; // listen for console messages on the server and send them to discord

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

	public function hasFlag(int $flag) : bool {
		return ($this->flags & (1 << $flag)) > 0;
	}

	public function setFlag(int $flag, bool $value = true) : void {
		if($this->hasFlag($flag) !== $value) {
			$this->flags ^= 1 << $flag;
		}
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
			"flags" => $this->flags,
		];
	}

	public function fromArray(array $data) : void {
		$this->serverAlias = $data["name"];
		$this->discordChannelId = $data["id"];
		$this->flags = $data["flags"];
	}

}
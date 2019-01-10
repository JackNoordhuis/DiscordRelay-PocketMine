<?php

/**
 * RelayOptionsPM-Discord-Relay
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

class RelayOptions implements \Serializable {

	/** @var string */
	private $token;

	/** @var string */
	private $defaultChannelId = null;

	/** @var RelayChannel[] */
	private $channels = [];

	public function token() : string {
		return $this->token;
	}

	public function setToken(string $token) : void {
		$this->token = $token;
	}

	/**
	 * @return RelayChannel
	 */
	public function defaultChannel() {
		return $this->channels[$this->defaultChannelId];
	}

	/**
	 * @return string|null
	 */
	public function defaultChannelId() : ?string {
		return $this->defaultChannelId;
	}

	/**
	 * @param string $id
	 */
	public function setDefaultChannel(string $id) : void {
		if($this->defaultChannelId !== null) {
			throw new \BadMethodCallException("Unable to modify default channel id after it has been set.");
		}

		if(isset($this->channels[$id])) {
			$this->defaultChannelId = $id;
		} else {
			throw new \BadMethodCallException("Cannot set the default channel to a one not managed by the relay.");
		}
	}

	/**
	 * @return RelayChannel[]
	 */
	public function channels() : array {
		return $this->channels;
	}

	/**
	 * @param string $id
	 *
	 * @return RelayChannel|null
	 */
	public function channel(string $id) : ?RelayChannel {
		return $this->channels[$id] ?? null;
	}

	public function addChannel(RelayChannel $channel) : void {
		if(isset($this->channels[$channel->id()])) {
			throw new \RuntimeException("Tried to register a relay channel twice!");
		}

		$this->channels[$channel->id()] = $channel;
	}

	public function serialize(bool $fast = false) {
		return json_encode($this->toArray(false, $fast));
	}

	public function unserialize($serialized) {
		$this->fromArray(json_decode($serialized, true));
	}

	public function fastUnserialize(string $serialized, RelayOptions $options) {
		$data = json_decode($serialized, true);

		$this->token = $data["token"];

		foreach($data["channels"] as $channelId) {
			$this->channels[$channelId] = $options->channel($channelId);
		}
	}

	public function __toString() {
		return json_encode($this->toArray());
	}

	public function toArray(bool $safe = true, bool $fast = false) : array {
		return [
			"token" => $safe ? str_repeat("*", 8) : $this->token,
			"channels" => array_map(function(RelayChannel $channel) use($fast) {
				return $fast ? $channel->id() : $channel->toArray();
			}, $this->channels),
		];
	}

	public function fromArray(array $data) : void {
		$this->token = $data["token"];
		foreach($data["channels"] as $key => $channel) {
			$this->channels[$key] = $c = new RelayChannel();
			$c->fromArray($channel);
		}
	}

}
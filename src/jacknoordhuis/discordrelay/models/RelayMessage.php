<?php

/**
 * RelayMessage.php – PM-Discord-Relay
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

namespace jacknoordhuis\discordrelay\models;

class RelayMessage implements \Serializable  {

	/** @var RelayChannel */
	private $channel;

	/** @var string */
	private $author;

	/** @var string */
	private $content;

	/**
	 * @return RelayChannel
	 */
	public function channel() : RelayChannel {
		return $this->channel;
	}

	/**
	 * @return string
	 */
	public function author() : string {
		return $this->author;
	}

	/**
	 * @return string
	 */
	public function content() : string {
		return $this->content;
	}

	/**
	 * @param RelayChannel $channel
	 */
	public function setChannel(RelayChannel $channel) : void {
		$this->channel = $channel;
	}

	/**
	 * @param string $author
	 */
	public function setAuthor(string $author) : void {
		$this->author = $author;
	}

	/**
	 * @param string $content
	 */
	public function setContent(string $content) : void {
		$this->content = $content;
	}

	public function serialize(bool $fast = false) {
		return json_encode($this->toArray($fast));
	}

	public function unserialize($serialized) {
		$this->fromArray(json_decode($serialized, true));
	}

	public function fastUnserialize(string $serialized, RelayOptions $options) {
		$data = json_decode($serialized, true);

		$this->channel = $options->channel($data["channel"]);

		$this->author = $data["author"];
		$this->content = $data["content"];
	}

	public function __toString() {
		return json_encode($this->toArray());
	}

	public function toArray(bool $fast = false) : array {
		return [
			"channel" => $fast ? $this->channel->id() : $this->channel->toArray(),
			"author" => $this->author,
			"content" => $this->content,
		];
	}

	public function fromArray(array $data) : void {
		$this->channel = new RelayChannel();
		$this->channel->fromArray($data["channel"]);

		$this->author = $data["author"];
		$this->content = $data["content"];
	}

}
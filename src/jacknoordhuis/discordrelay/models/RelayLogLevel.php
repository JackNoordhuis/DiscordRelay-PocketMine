<?php

/**
 * RelayLogLevel.php – PM-Discord-Relay
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

class RelayLogLevel {

	/** @var string */
	protected $level;

	/** @var bool */
	protected $embed;

	/** @var int */
	protected $embedColor;

	public function level() : string {
		return $this->level;
	}

	/**
	 * @param string $level
	 */
	public function setLevel(string $level) : void {
		$this->level = $level;
	}

	/**
	 * @return bool
	 */
	public function embed() : bool {
		return $this->embed;
	}

	/**
	 * @param bool $embed
	 */
	public function setEmbed(bool $embed) : void {
		$this->embed = $embed;
	}

	/**
	 * @return int
	 */
	public function embedColor() : int {
		return $this->embedColor;
	}

	/**
	 * @param int $color
	 */
	public function setEmbedColor(int $color) : void {
		if($color > 16777215) {
			throw new \InvalidArgumentException("Embed color must be less than 16777215");
		}

		$this->embedColor = $color;
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
			"level" => $this->level,
			"embed" => $this->embed,
			"embed_color" => $this->embedColor,
		];
	}

	public function fromArray(array $data) : void {
		$this->level = $data["level"];
		$this->embed = $data["embed"];
		$this->embedColor = $data["embed_color"];
	}

}
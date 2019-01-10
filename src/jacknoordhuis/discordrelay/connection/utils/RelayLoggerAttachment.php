<?php

/**
 * RelayLoggerAttachmentPM-Discord-Relay
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

namespace jacknoordhuis\discordrelay\connection\utils;

use pocketmine\utils\TextFormat;

class RelayLoggerAttachment extends \ThreadedLoggerAttachment {

	/** @var \Threaded */
	private $outboundMessages;

	public function __construct() {
		$this->outboundMessages = new \Threaded;
	}

	public function getOutboundMessage() {
		return $this->outboundMessages->shift();
	}

	public function log($level, $message) {
		$this->outboundMessages[] = serialize([
			"message" => TextFormat::clean($message),
			"level" => $level,
		]);
	}

}
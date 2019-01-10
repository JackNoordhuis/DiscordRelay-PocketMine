<?php

/**
 * HandlerMethod.php – PM-Discord-Relay
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

namespace jacknoordhuis\discordrelay\event\utils;

use pocketmine\event\EventPriority;

class HandlerMethod {

	/** @var string */
	protected $method;

	/** @var string */
	protected $event;

	/** @var int */
	protected $priority = EventPriority::NORMAL;

	/** @var bool */
	protected $ignoreCancelled = true;

	public function __construct(string $methodName) {
		$this->method = $methodName;
	}

	/**
	 * @return string
	 */
	public function fetchMethod() : string {
		return $this->method;
	}

	/**
	 * @return string
	 */
	public function fetchEvent() : string {
		return $this->event;
	}

	/**
	 * @return int
	 */
	public function fetchPriority() : int {
		return $this->priority;
	}

	/**
	 * @return bool
	 */
	public function fetchIgnoreCancelled() : bool {
		return $this->ignoreCancelled;
	}

	/**
	 * Set the handler methods event to listen for.
	 *
	 * @param string $eventClass
	 *
	 * @return HandlerMethod
	 */
	public function event(string $eventClass) : HandlerMethod {
		$this->event = $eventClass;

		return $this;
	}

	/**
	 * Set the handler methods priority.
	 *
	 * @param int $priority
	 *
	 * @return HandlerMethod
	 */
	public function priority(int $priority) : HandlerMethod {
		$this->priority = $priority;

		return $this;
	}

	/**
	 * Set the handler methods ignore cancelled option.
	 *
	 * @param bool $ignore
	 *
	 * @return HandlerMethod
	 */
	public function ignoreCancelled(bool $ignore) : HandlerMethod {
		$this->ignoreCancelled = $ignore;

		return $this;
	}

}
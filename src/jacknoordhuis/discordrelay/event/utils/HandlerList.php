<?php

/**
 * HandlerList– PM-Discord-Relay
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

use jacknoordhuis\discordrelay\event\EventHandler;

class HandlerList {

	/** @var EventHandler */
	private $handler;

	/** @var HandlerMethod[] */
	protected $handlers = [];

	public function __construct(EventHandler $handler) {
		$this->handler = $handler;
	}

	/**
	 * @return EventHandler
	 */
	public function getHandler() : EventHandler {
		return $this->handler;
	}

	/**
	 * @return HandlerMethod[]
	 */
	public function handlers() : array {
		return $this->handlers;
	}

	/**
	 * Create a new event handler from a method on the handler class.
	 *
	 * @param string $methodName
	 *
	 * @return HandlerMethod
	 */
	public function handler(string $methodName) : HandlerMethod {
		return ($this->handlers[] = new HandlerMethod($methodName));
	}

}
<?php

/**
 * EventHandler.php – PM-Discord-Relay
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

namespace jacknoordhuis\discordrelay\event;

use jacknoordhuis\discordrelay\event\utils\HandlerList;
use pocketmine\event\Listener;

abstract class EventHandler implements Listener {

	/** @var EventManager */
	private $manager;

	/**
	 * @param EventManager|null $manager
	 */
	public function setManager(?EventManager $manager) : void {
		$this->manager = $manager;
	}

	/**
	 * @return EventManager
	 */
	public function getManager() : EventManager {
		return $this->manager;
	}

	/**
	 * Builds a list of events that the handler listens for.
	 *
	 * @param HandlerList $list
	 */
	abstract public function handles(HandlerList $list) : void;

}
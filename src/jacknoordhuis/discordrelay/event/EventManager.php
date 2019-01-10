<?php

/**
 * EventManager.php – PM-Discord-Relay
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

use jacknoordhuis\discordrelay\DiscordRelay;
use jacknoordhuis\discordrelay\event\utils\HandlerList;
use pocketmine\plugin\MethodEventExecutor;

class EventManager {

	/** @var DiscordRelay */
	private $plugin;

	/** @var EventHandler[] */
	private $eventHandlers = [];

	public function __construct(DiscordRelay $plugin) {
		$this->plugin = $plugin;
	}

	/**
	 * @return DiscordRelay
	 */
	public function getPlugin() : DiscordRelay {
		return $this->plugin;
	}

	/**
	 * Register an event handler to the pocketmine event manager.
	 *
	 * @param EventHandler $handler
	 */
	public function registerHandler(EventHandler $handler) : void {
		$handler->handles($list = new HandlerList($handler));
		foreach($list->handlers() as $method) {
			$this->plugin->getServer()->getPluginManager()->registerEvent($method->fetchEvent(), $handler, $method->fetchPriority(), new MethodEventExecutor($method->fetchMethod()), $this->plugin, $method->fetchIgnoreCancelled());
			$this->plugin->getLogger()->debug("Registered listener for " . (new \ReflectionClass($method->fetchEvent()))->getShortName() . " for " . (new \ReflectionObject($handler))->getShortName() . "::" . $method->fetchMethod() . "()");
		}

		$this->eventHandlers[] = $handler;
		$handler->setManager($this);
	}

}
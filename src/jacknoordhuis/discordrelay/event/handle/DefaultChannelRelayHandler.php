<?php

/**
 * DefaultChannelRelayHandler.phpndler.php – PM-Discord-Relay
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

namespace jacknoordhuis\discordrelay\event\handle;

use jacknoordhuis\discordrelay\event\EventHandler;
use jacknoordhuis\discordrelay\event\utils\HandlerList;
use jacknoordhuis\discordrelay\models\RelayMessage;
use pocketmine\event\EventPriority;
use pocketmine\event\player\PlayerChatEvent;

class DefaultChannelRelayHandler extends EventHandler {

	public function handles(HandlerList $list) : void {
		$list->handler("handlePlayerChat")
			->event(PlayerChatEvent::class)
			->priority(EventPriority::MONITOR)
			->ignoreCancelled(true);
	}

	public function handlePlayerChat(PlayerChatEvent $event) : void {
		$plugin = $this->getManager()->getPlugin();

		$message = new RelayMessage();
		$message->setChannel($plugin->getRelayOptions()->defaultChannel());
		$message->setAuthor($event->getPlayer()->getName());
		$message->setContent($event->getMessage());

		$plugin->getRelayThread()->pushOutboundMessage($message->serialize(true));
	}

}
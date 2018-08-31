<?php

/**
 * RelayThread.phpiscord-Relay
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

namespace jacknoordhuis\discordrelay\connection;

use jacknoordhuis\discordrelay\utils\AutoloaderLoader;
use jacknoordhuis\discordrelay\connection\models\RelayOptions;
use pocketmine\snooze\SleeperNotifier;
use pocketmine\Thread;
use pocketmine\utils\MainLogger;
use pocketmine\utils\Utils;

class RelayThread extends Thread {

	/** @var \AttachableThreadedLogger */
	protected $logger;

	/** @var string */
	protected $options;

	/** @var SleeperNotifier */
	protected $mainThreadNotifier;

	/** @var bool */
	private $shutdown = false;

	public function __construct(\AttachableThreadedLogger $logger, string $options, SleeperNotifier $sleeper) {
		$this->logger = $logger;
		$this->options = $options;
		$this->mainThreadNotifier = $sleeper;

		$this->start(PTHREADS_INHERIT_INI | PTHREADS_INHERIT_CONSTANTS);
	}

	public function run() {
		error_reporting(-1);

		$this->registerClassLoader();
		AutoloaderLoader::load();

		//set this after the autoloader is registered
		set_error_handler([Utils::class, 'errorExceptionHandler']);
		register_shutdown_function([$this, "shutdownHandler"]);

		if($this->logger instanceof MainLogger){
			$this->logger->registerStatic();
		}

		gc_enable();

		try {
			new RelayManager($this); // run all the relay logic from a non-thread/threaded class to avoid any unwanted serialization
		} catch(\Throwable $e) {
			$this->getLogger()->logException($e);
		}
	}

	public function getLogger() : \AttachableThreadedLogger {
		return $this->logger;
	}

	public function getOptions() : RelayOptions {
		$options = new RelayOptions();
		$options->unserialize($this->options);
		return $options;
	}

	public function handleException(\Throwable $e) {
		$this->logger->logException($e);
	}

	public function isShutdown() : bool {
		return $this->shutdown;
	}

	public function shutdown() : void {
		$this->shutdown = true;
	}

	public function shutdownHandler() {
		if($this->shutdown !== true){
			$this->getLogger()->emergency("RelayThread crashed!");
		}
	}

}
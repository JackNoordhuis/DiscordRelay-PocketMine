<?php

/**
 * DiscordTextFormat.phpat.php – PM-Discord-Relay
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

namespace jacknoordhuis\discordrelay\utils;

use LogLevel;

class DiscordTextFormat {

	public static function ALL_LOG_LEVELS() : array {
		return [LogLevel::EMERGENCY, LogLevel::ALERT, LogLevel::CRITICAL, LogLevel::ERROR, LogLevel::WARNING, LogLevel::NOTICE, LogLevel::INFO, LogLevel::DEBUG];
	}

	public const BLACK = 1;
	public const DARK_BLUE = 170;
	public const DARK_GREEN = 43520;
	public const DARK_AQUA = 43690;
	public const DARK_RED = 11141120;
	public const DARK_PURPLE = 11141290;
	public const GOLD = 16755200;
	public const GRAY = 11184810;
	public const DARK_GRAY = 5592405;
	public const BLUE = 5592575;
	public const GREEN = 5635925;
	public const AQUA = 5636095;
	public const RED = 16733525;
	public const LIGHT_PURPLE = 16733695;
	public const YELLOW = 16777045;
	public const WHITE = 16777215;

	/**
	 * Convert a log level to a discord embed color.
	 *
	 * @param string $level
	 *
	 * @return int
	 */
	public static function logLevelToColor(string $level) : int {
		switch($level){
			case LogLevel::EMERGENCY:
			case LogLevel::ALERT:
			case LogLevel::CRITICAL:
				return self::RED;
			case LogLevel::ERROR:
				return self::DARK_RED;
			case LogLevel::WARNING:
				return self::YELLOW;
			case LogLevel::NOTICE:
				return self::AQUA;
			case LogLevel::INFO:
				return self::WHITE;
			case LogLevel::DEBUG:
				return self::GRAY;
		}

		throw new \InvalidArgumentException("Unhandled log level to discord color conversion: level: " . $level);
	}

	/**
	 * Convert a log level into a string.
	 *
	 * @param string $level
	 *
	 * @return string
	 */
	public static function logLevelToString(string $level) : string {
		return ucfirst($level);
	}

	/**
	 * Convert a log name into a level.
	 *
	 * @param string $level
	 *
	 * @return string
	 */
	public static function logStringToLevel(string $level) : string {
		switch(strtolower($level)){
			case "emergency":
				return LogLevel::EMERGENCY;
			case "alert":
				return LogLevel::ALERT;
			case "critical":
				return LogLevel::CRITICAL;
			case "error":
				return LogLevel::ERROR;
			case "warning":
				return LogLevel::WARNING;
			case "notice":
				return LogLevel::NOTICE;
			case "info":
				return LogLevel::INFO;
			case "debug":
				return LogLevel::DEBUG;
		}

		throw new \InvalidArgumentException("Unhandled log level to string conversion: level: " . $level);
	}

}
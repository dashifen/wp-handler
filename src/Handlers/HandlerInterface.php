<?php

namespace Dashifen\WPHandler\Handlers;

use Throwable;

/**
 * Interface HandlerInterface
 * @package Dashifen\WPHandler\Handlers
 */
interface HandlerInterface {
	/**
	 * /**
	 * initialize
	 *
	 * Uses protected addAction() and addFilter() to connect WordPress to
	 * this object.
	 *
	 * @return void
	 */
	public function initialize(): void;

	/**
	 * debug
	 *
	 * Given stuff, print information about it and then die() if
	 * the $die flag is set.
	 *
	 * @param mixed $stuff
	 * @param bool $die
	 *
	 * @return void
	 */
	public static function debug($stuff, $die = false): void;

	/**
	 * writeLog
	 *
	 * Calling this method should write $data to the WordPress debug.log file.
	 *
	 * @param mixed $data
	 *
	 * @return void
	 */
	public static function writeLog($data): void;

	/**
	 * isDebug
	 *
	 * Returns true when WP_DEBUG exists and is set.
	 *
	 * @return bool
	 */
	public static function isDebug(): bool;

	/**
	 * catcher
	 *
	 * This serves as a general-purpose Exception handler which displays
	 * the caught object when we're debugging and writes it to the log when
	 * we're not.
	 *
	 * @param Throwable $thrown
	 *
	 * @return void
	 */
	public static function catcher(Throwable $thrown): void;

	/**
	 * __call
	 *
	 * Magic methods are always a part of the interface, but this time we
	 * need this one, so by declaring it here, PHP will throw a tantrum if
	 * it's not defined.
	 *
	 * @param string $method
	 * @param array  $arguments
	 *
	 * @return mixed
	 */
	public function __call(string $method, array $arguments);

	/**
	 * __toString
	 *
	 * Magic methods are always a part of the interface, but this time we
	 * need this one, so by declaring it here, PHP will throw a tantrum if
	 * it's not defined.
	 *
	 * @return string
	 */
	public function __toString(): string;
}
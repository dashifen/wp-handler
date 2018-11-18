<?php

namespace Engage\WordPress\Handlers;

/**
 * Interface HandlerInterface
 * @package Engage\WordPress\Handlers
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
	public function initialize();

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
	public static function debug($stuff, $die = false);

	/**
	 * writeLog
	 *
	 * Calling this method should write $data to the WordPress debug.log file.
	 *
	 * @param mixed $data
	 *
	 * @return void
	 */
	public static function writeLog($data);

	/**
	 * isDebug
	 *
	 * Returns true when WP_DEBUG exists and is set.
	 *
	 * @return bool
	 */
	public static function isDebug();

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
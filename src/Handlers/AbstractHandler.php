<?php

/** @noinspection PhpUndefinedClassInspection */
/** @noinspection PhpUndefinedFunctionInspection */

namespace Engage\WordPress\Handlers;

use Engage\WordPress\Hooks\Hook;
use Engage\WordPress\Hooks\HookException;

/**
 * Class AbstractHandler
 *
 * This object defines a common set of methods for WordPress themes that
 * allow a very simple public interface and access to protected methods as
 * WordPress hooks preventing accidental/malicious execution of a theme's
 * methods elsewhere.
 *
 * @package Engage\WordPress\Handlers
 */
abstract class AbstractHandler implements HandlerInterface {
	/**
	 * @var array
	 */
	protected $hooked = [];

	/**
	 * @var string
	 */
	protected $dir;

	/**
	 * @var string
	 */
	protected $url;

	/**
	 * AbstractHandler constructor.
	 */
	public function __construct() {
		$this->dir = get_stylesheet_directory();
		$this->url = get_stylesheet_directory_uri();
	}

	/**
	 * debug
	 *
	 * Given stuff, print information about it and then die() if
	 * the $die flag is set.
	 *
	 * @param mixed $stuff
	 * @param bool  $die
	 *
	 * @return void
	 */
	public static function debug($stuff, $die = false) {
		if (!self::isDebug()) {

			// this return ensures that we don't print debugging statements
			// on installations where WP debugging is turned off.

			return;
		}

		$message = "<pre>" . print_r($stuff, true) . "</pre>";

		if (!$die) {
			echo $message;
			return;
		}

		die($message);
	}

	/**
	 * writeLog
	 *
	 * Calling this method should write $data to the WordPress debug.log file.
	 *
	 * @param mixed $data
	 *
	 * @return void
	 */
	public static function writeLog($data) {

		// source:  https://www.elegantthemes.com/blog/tips-tricks/using-the-wordpress-debug-log
		// accessed:  2018-07-09

		if (!function_exists("write_log")) {
			function write_log($log) {
				if (is_array($log) || is_object($log)) {
					error_log(print_r($log, true));
				} else {
					error_log($log);
				}
			}
		}

		write_log($data);
	}

	/**
	 * isDebug
	 *
	 * Returns true when WP_DEBUG exists and is set.
	 *
	 * @return bool
	 */
	public static function isDebug() {
		return defined("WP_DEBUG") && WP_DEBUG;
	}


	/**
	 * __call
	 *
	 * Checks to see if the method being called is in the $hooked
	 * property, and if so, calls it passing the $arguments to it.
	 *
	 * @param string $method
	 * @param array  $arguments
	 *
	 * @return mixed
	 * @throws HandlerException
	 */
	public function __call(string $method, array $arguments) {
		if (!in_array($method, $this->hooked)) {
			throw new HandlerException("Unhooked method: $method.",
				HandlerException::UNHOOKED_METHOD);
		}

		$key = $this->getHookIndex($method);
		if (!array_key_exists($key, $this->hooked)) {

			// yes, we did these function calls in getHookIndex() below,
			// but if they failed, doing them again before we die isn't going
			// to waste that much additional time.  and, it's easier than
			// returning a bunch of data from getHookIndex() when, hopefully
			// this never happens.

			$action = current_action();
			$priority = has_filter($action, [$this, $method]);
			throw new HandlerException("$method is hooked, but not at $action:$priority.",
				HandlerException::INAPPROPRIATE_CALL);
		}

		// we return the results of our method call because some of them
		// might be filters.  for more information on the splat operator
		// used below: https://bit.ly/2zqHQCK.

		return $this->{$method}(...$arguments);
	}

	/**
	 * getHookIndex
	 *
	 * Given the name of a method, returns the index at which it's
	 * hook is expected to be.
	 *
	 * @param string $method
	 *
	 * @return string
	 */
	protected function getHookIndex(string $method) {
		$action = current_action();

		// the has_filter() WordPress function returns a boolean when it
		// receives only the name of an action/filter.  but, when it also
		// gets a callback, it returns the priority at which that callback
		// will execute.  with that, we can re-build the hook index for
		// this method at the current action.

		$priority = has_filter($action, [$this, $method]);
		return Hook::getHookIndex($action, $this, $method, $priority);
	}

	/**
	 * @return string
	 */
	public function __toString(): string {
		return static::class;
	}

	/**
	 * initialize
	 *
	 * Uses addAction() and addFilter() to connect WordPress to the methods
	 * of this object's child which are intended to be protected.
	 *
	 * @return void
	 */
	abstract public function initialize();

	/**
	 * addAction
	 *
	 * Passes its arguments to add_action() and adds $method to the
	 * $hooked property.
	 *
	 * @param string $hook
	 * @param string $method
	 * @param int    $priority
	 * @param int    $arguments
	 *
	 * @return void
	 * @throws HookException
	 */
	protected function addAction(string $hook, string $method, int $priority = 10, int $arguments = 1) {

		add_action($hook, [$this, $method], $priority, $arguments);
		$hookIndex = Hook::getHookIndex($hook, $this, $method, $priority);
		$this->hooked[$hookIndex] = new Hook($hook, $this, $method, $priority, $arguments);
	}

	/**
	 * removeAction
	 *
	 * Removes a hooked method from WP core and the record of the hook
	 * from our $hooked properties.
	 *
	 * @param string $hook
	 * @param string $method
	 * @param int    $priority
	 *
	 * @return void
	 */
	protected function removeAction(string $hook, string $method, int $priority = 10) {
		remove_action($hook, [$this, $method], $priority);
		$hookIndex = Hook::getHookIndex($hook, $this, $method, $priority);
		unset($this->hooked[$hookIndex]);
	}

	/**
	 * addFilter
	 *
	 * Passes its arguments to add_filter() and adds $method to  the
	 * $hooked property.
	 *
	 * @param string $hook
	 * @param string $method
	 * @param int    $priority
	 * @param int    $arguments
	 *
	 * @return void
	 *
	 * @throws HookException
	 */
	protected function addFilter(string $hook, string $method, int $priority = 10, int $arguments = 1) {
		add_filter($hook, [$this, $method], $priority, $arguments);
		$hookIndex = Hook::getHookIndex($hook, $this, $method, $priority);
		$this->hooked[$hookIndex] = new Hook($hook, $this, $method, $priority, $arguments);
	}

	/**
	 * removeFilter
	 *
	 * Removes a filter from WP and the record of the hooked method
	 * from the $hooked property.
	 *
	 * @param string $hook
	 * @param string $method
	 * @param int    $priority
	 *
	 * @return void
	 */
	protected function removeFilter(string $hook, string $method, int $priority = 10) {
		remove_filter($hook, [$this, $method], $priority);
		$hookIndex = Hook::getHookIndex($hook, $this, $method, $priority);
		unset($this->hooked[$hookIndex]);
	}
}
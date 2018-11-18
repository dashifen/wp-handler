<?php

namespace Dashifen\WPHandler\Hooks;
use Dashifen\WPHandler\Handlers\HandlerInterface;

/**
 * Interface HookInterface
 * @package Dashifen\WPHandler\Hooks
 */
interface HookInterface {
	/**
	 * getHookIndex
	 *
	 * Given its parameters, this function returns a unique index for
	 * this hook.  it's needed when removing one of our hooks from WP.
	 *
	 * @param string           $hook
	 * @param HandlerInterface $object
	 * @param string           $method
	 * @param int              $priority
	 *
	 * @return string
	 */
	public static function getHookIndex(string $hook, HandlerInterface $object, string $method, int $priority = 10): string;

	/**
	 * __toString
	 *
	 * While magic methods are always a part of an object's interface, Hooks
	 * need to implement this one so that we can quickly find them in the
	 * AbstractTheme's __call() method.
	 *
	 * @return string
	 */
	public function __toString(): string;

	/**
	 * @return string
	 */
	public function getHook(): string;

	/**
	 * @param string $hook
	 *
	 * @return void
	 */
	public function setHook(string $hook);

	/**
	 * @return HandlerInterface
	 */
	public function getObject(): HandlerInterface;

	/**
	 * @param HandlerInterface $theme
	 *
	 * @return void
	 */
	public function setObject(HandlerInterface $theme);



	/**
	 * @return string
	 */
	public function getMethod(): string;

	/**
	 * @param string $method
	 *
	 * @return void
	 */
	public function setMethod(string $method);

	/**
	 * @return int
	 */
	public function getPriority(): int;

	/**
	 * @param int $priority
	 *
	 * @return void
	 */
	public function setPriority(int $priority);

	/**
	 * @return int
	 */
	public function getArgumentCount(): int;

	/**
	 * @param int $argumentCount
	 *
	 * @return void
	 */
	public function setArgumentCount(int $argumentCount);
}
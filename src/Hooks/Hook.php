<?php

namespace Dashifen\WPHandler\Hooks;

use Dashifen\WPHandler\Handlers\HandlerInterface;
use ReflectionException;
use ReflectionMethod;
use ReflectionClass;

/**
 * Class Hook
 * @package Dashifen\WPHandler\Hooks
 */
class Hook implements HookInterface {
	/**
	 * @var string
	 */
	protected $hook;

	/**
	 * @var HandlerInterface
	 */
	protected $object;

	/**
	 * @var string
	 */
	protected $method;

	/**
	 * @var int
	 */
	protected $priority = 10;

	/**
	 * @var int
	 */
	protected $argumentCount = 1;

	/**
	 * Hook constructor.
	 *
	 * @param string           $hook
	 * @param HandlerInterface $object
	 * @param string           $method
	 * @param int              $priority
	 * @param int              $argumentCount
	 *
	 * @throws HookException
	 */
	public function __construct(
		string $hook,
		HandlerInterface $object,
		string $method,
		int $priority = 10,
		int $argumentCount = 1
	) {
		$this->setHook($hook);
		$this->setObject($object);
		$this->setMethod($method);
		$this->setPriority($priority);
		$this->setArgumentCount($argumentCount);
	}

	/**
	 * __toString
	 *
	 * Returns the name of the method that this hook executes.  This is
	 * used in the AbstractTheme's __cal() method to search through a list
	 * of hooked methods.
	 *
	 * @return string
	 */
	public function __toString(): string {
		return $this->getMethod();
	}

	public static function getHookIndex(
		string $hook,
		HandlerInterface $object,
		string $method,
		int $priority = 10
	): string {

		// to create an index for this hook, we just concatenate our
		// parameters together using vsprintf() and func_get_args() to
		// make it look all pretty.  note:  ThemeInterface objects
		// implements __toString() so we can use the object itself as
		// a string here.

		return vsprintf("%s:%s:%s:%s", func_get_args());
	}


	/**
	 * @return string
	 */
	public function getHook(): string {
		return $this->hook;
	}

	/**
	 * @param string $hook
	 */
	public function setHook(string $hook) {
		$this->hook = $hook;
	}

	/**
	 * @return HandlerInterface
	 */
	public function getObject(): HandlerInterface {
		return $this->object;
	}

	/**
	 * @param HandlerInterface $object
	 */
	public function setObject(HandlerInterface $object) {
		$this->object = $object;
	}

	/**
	 * @return string
	 */
	public function getMethod(): string {
		return $this->method;
	}

	/**
	 * @param string $method
	 *
	 * @throws HookException
	 */
	public function setMethod(string $method) {
		if (!($this->object instanceof HandlerInterface)) {
			throw new HookException("Please set a hook's object before method.", HookException::OBJECT_NOT_FOUND);
		}

		try {
			$reflection = new ReflectionClass($this->object);
			$methods = array_map([$this, "getMethodName"], $reflection->getMethods());
			if (!in_array($method, $methods )) {

				// technically, this has nothing to do with our Reflection, but
				// throwing a ReflectionException here makes our catch-block
				// much easier.

				throw new ReflectionException();
			}
		} catch (ReflectionException $e) {

			// to ensure that we only throw HookExceptions out of this method,
			// we catch our ReflectionException and then just throw a
			// HookException instead.

			throw new HookException("Method not found: $method.", HookException::METHOD_NOT_FOUND);
		}

		$this->method = $method;
	}

	/**
	 * getMethodName
	 *
	 * Given a ReflectionMethod, returns its name.
	 *
	 * @param ReflectionMethod $method
	 *
	 * @return string
	 */
	protected function getMethodName(ReflectionMethod $method): string {
		return $method->name;
	}

	/**
	 * @return int
	 */
	public function getPriority(): int {
		return $this->priority;
	}

	/**
	 * @param int $priority
	 */
	public function setPriority(int $priority) {
		$this->priority = $priority;
	}

	/**
	 * @return int
	 */
	public function getArgumentCount(): int {
		return $this->argumentCount;
	}

	/**
	 * @param int $argumentCount
	 *
	 * @throws HookException
	 */
	public function setArgumentCount(int $argumentCount) {
		if ($argumentCount < 0) {
			throw new HookException("Invalid argument count: $argumentCount.", HookException::INVALID_ARGUMENT_COUNT);
		}

		$this->argumentCount = $argumentCount;
	}
}
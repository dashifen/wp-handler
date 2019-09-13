<?php

namespace Dashifen\WPHandler\Hooks;

use ReflectionClass;
use ReflectionException;
use Dashifen\Repository\Repository;
use Dashifen\Repository\RepositoryException;
use Dashifen\WPHandler\Handlers\HandlerInterface;

/**
 * Class Hook
 *
 * @property-read string           $hook
 * @property-read HandlerInterface $object
 * @property-read string           $method
 * @property-read int              $priority
 * @property-read int              $argumentCount
 * @package Dashifen\WPHandler\Hooks
 */
class Hook extends Repository implements HookInterface {
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

  /** @noinspection PhpDocRedundantThrowsInspection */

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
   * @throws RepositoryException
   */
  public function __construct (
    string $hook,
    HandlerInterface $object,
    string $method,
    int $priority = 10,
    int $argumentCount = 1
  ) {
    parent::__construct([
      "hook"          => $hook,
      "object"        => $object,
      "method"        => $method,
      "priority"      => $priority,
      "argumentCount" => $argumentCount
    ]);
  }

  /**
   * @param string $hook
   */
  public function setHook (string $hook): void {
    $this->hook = $hook;
  }

  /**
   * @param HandlerInterface $object
   */
  public function setObject (HandlerInterface $object): void {
    $this->object = $object;
  }

  /**
   * @param string $method
   *
   * @throws HookException
   */
  public function setMethod (string $method): void {
    if (!($this->object instanceof HandlerInterface)) {
      throw new HookException("Please set a hook's object before method.", HookException::OBJECT_NOT_FOUND);
    }

    try {

      // to make sure that the object we're working with actually has the
      // method we're trying to hook, we'll make a reflection of it.  in the
      // pasts, reflections were very expensive, but recent versions of PHP
      // make them very fast, especially if the object is already loaded
      // which our handler should be.  once we have the reflection, we can
      // get it's list of methods and see if this one is in it.

      $reflection = new ReflectionClass($this->object);
      foreach ($reflection->getMethods() as $reflectionMethod) {
        $methodShortName = $reflectionMethod->getShortName();
        if ($methodShortName === $method) {

          // if we find the method we're looking for, we'll set our property
          // and return.  we hope that this should save some time for objects
          // with a lot of methods  because we should only have to compare
          // short names to $method for the ones prior to our match skipping
          // the rest.

          $this->method = $method;
          return;
        }
      }

      // if we made it down here, we're going to throw a ReflectionException.
      // not finding a match isn't technically a problem within the reflection,
      // but it makes it easier to throw only HookExceptions by simply
      // triggering the catch block for both them and the more simply not-found
      // problem.

      throw new HookException("Method not found: $method",
        HookException::METHOD_NOT_FOUND);

    } catch (ReflectionException $e) {

      // to ensure that we only throw HookExceptions out of this method,
      // we catch our ReflectionException and then just throw a
      // HookException instead.

      throw new HookException("Unable to accurately reflect " . $this->object,
        HookException::OBJECT_NOT_FOUND);
    }
  }

  /**
   * @param int $priority
   */
  public function setPriority (int $priority = 10): void {
    $this->priority = $priority;
  }

  /**
   * @param int $argumentCount
   *
   * @throws HookException
   */
  public function setArgumentCount (int $argumentCount = 1): void {
    if ($argumentCount < 0) {
      throw new HookException("Invalid argument count: $argumentCount.", HookException::INVALID_ARGUMENT_COUNT);
    }

    $this->argumentCount = $argumentCount;
  }
}
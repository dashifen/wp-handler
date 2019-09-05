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
      $reflection = new ReflectionClass($this->object);
      $methods = array_map([$this, "getMethodName"], $reflection->getMethods());
      if (!in_array($method, $methods)) {

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
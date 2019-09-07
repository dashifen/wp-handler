<?php

namespace Dashifen\WPHandler\Hooks;

use Dashifen\WPHandler\Handlers\HandlerInterface;

/**
 * Interface HookInterface
 *
 * We wouldn't usually specify properties on an interface, but because the
 * HookCollectionInterface specifies that it returns implementations of this
 * interface, doing so helps some IDEs to know what properties we expect
 * those implementations to have.
 *
 * @property-read string           $hook
 * @property-read HandlerInterface $object
 * @property-read string           $method
 * @property-read int              $priority
 * @property-read int              $argumentCount
 *
 * @package Dashifen\WPHandler\Hooks
 */
interface HookInterface {
  /**
   * setHook
   *
   * Sets the hook property representing the WordPress action or filter at
   * which our callback shall be executed.
   *
   * @param string $hook
   *
   * @return void
   */
  public function setHook (string $hook): void;

  /**
   * setObject
   *
   * Sets the object property which is the HandlerInterface object
   * which contains our callback method.
   *
   * @param HandlerInterface $theme
   *
   * @return void
   */
  public function setObject (HandlerInterface $theme): void;

  /**
   * setMethod
   *
   * Sets the method property.
   *
   * @param string $method
   *
   * @return void
   */
  public function setMethod (string $method): void;

  /**
   * setPriority
   *
   * Sets the priority property
   *
   * @param int $priority
   *
   * @return void
   */
  public function setPriority (int $priority = 10): void;

  /**
   * setArgumentCount
   *
   * Sets the number of arguments that will be passed to our callback method.
   *
   * @param int $argumentCount
   *
   * @return void
   */
  public function setArgumentCount (int $argumentCount = 1): void;
}
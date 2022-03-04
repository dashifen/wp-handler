<?php

namespace Dashifen\WPHandler\Hooks;

use Closure;
use Dashifen\Repository\RepositoryException;

/**
 * Class ClosureHook
 *
 * The purpose of this class is mostly to provide symmetry with MethodHooks and
 * because we might one day find a use for it beyond simply recording that that
 * an action will be taken at a specific hook and priority within a hook
 * collection.
 *
 * @property-read Closure $callback
 *
 * @package Dashifen\WPHandler\Hooks
 */
class ClosureHook extends AbstractHook
{
  protected Closure $callback;
  
  /**
   * ClosureHook constructor.
   *
   * @param string  $hook
   * @param Closure $callback
   * @param int     $priority
   * @param int     $argumentCount
   *
   * @throws HookException
   */
  public function __construct(string $hook, Closure $callback, int $priority = 10, int $argumentCount = 1)
  {
    try {
      parent::__construct(
        [
          'hook'          => $hook,
          'callback'      => $callback,
          'priority'      => $priority,
          'argumentCount' => $argumentCount,
        ]
      );
    } catch (RepositoryException $e) {
      // to avoid the calling scopes needing to know about this
      // RepositoryException, we're going to convert it to a HookException
      // which is more specific to this context.
      
      throw $this->convertException($e, HookException::FAILURE_TO_CONSTRUCT);
    }
  }
  
  /**
   * setCallback
   *
   * Sets the callback property.
   *
   * @param Closure $callback
   *
   * @return void
   */
  protected function setCallback(Closure $callback)
  {
    $this->callback = $callback;
  }
}

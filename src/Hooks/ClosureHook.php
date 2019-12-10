<?php

namespace Dashifen\WPHandler\Hooks;

use Closure;
use Dashifen\Repository\RepositoryException;

/**
 * Class ClosureHook
 *
 * @property-read Closure $callback
 *
 * @package Dashifen\WPHandler\Hooks
 */
class ClosureHook extends AbstractHook {
  /**
   * @var Closure
   */
  protected $callback;

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
  public function __construct (string $hook, Closure $callback, int $priority = 10, int $argumentCount = 1) {
    try {
      parent::__construct([
        'hook'          => $hook,
        'callback'      => $callback,
        'priority'      => $priority,
        'argumentCount' => $argumentCount
      ]);
    } catch (RepositoryException $e) {

      // to avoid the calling scopes needing to know about this
      // RepositoryException, we're going to convert it to a HookException
      // which is more specific to this context.

      throw $this->convertException($e, HookException::FAILURE_TO_CONSTRUCT);
    }
  }
}

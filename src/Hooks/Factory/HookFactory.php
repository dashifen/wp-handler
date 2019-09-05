<?php

namespace Dashifen\WPHandler\Hooks\Factory;

use Dashifen\WPHandler\Hooks\Hook;
use Dashifen\WPHandler\Hooks\HookException;
use Dashifen\WPHandler\Hooks\HookInterface;
use Dashifen\Repository\RepositoryException;
use Dashifen\WPHandler\Handlers\HandlerInterface;

class HookFactory implements HookFactoryInterface {
  /**
   * produceHook
   *
   * Returns an implementation of HookInterface to the calling scope.
   *
   * @param string           $hook
   * @param HandlerInterface $object
   * @param string           $method
   * @param int              $priority
   * @param int              $argumentCount
   *
   * @return HookInterface
   * @throws HookException
   * @throws RepositoryException
   */
  public function produceHook (string $hook, HandlerInterface $object, string $method, int $priority = 10, int $argumentCount = 1): HookInterface {

    // the purpose of this function is to provide a single place where
    // HookInterface implementations are constructed.  thus, when a plugin
    // or theme needs to modify the default Hook object to perform in some
    // other way, a new HookFactory can produce that object and be added to
    // the appropriate handlers.

    return new Hook($hook, $object, $method, $priority, $argumentCount);
  }

  /**
   * produceHookIndex
   *
   * Returns a string that can be used as an array index in the calling scope.
   *
   * @param string           $hook
   * @param HandlerInterface $object
   * @param string           $method
   * @param int              $priority
   *
   * @return string
   */
  public function produceHookIndex (string $hook, HandlerInterface $object, string $method, int $priority): string {

    // like the prior method, this is to provide a way to get a hook index
    // if an implementation of the HookInterface needs to change the default
    // indexing structure of a handler's hooks.  it is very likely that we
    // might change the prior method without this one; only if a HookInterface
    // object constructs its indices differently that the default Hook object
    // do we need to change this.  by default, we simply join our parameters
    // like strings and return the resulting string to the calling scope.

    $format = join(":", array_fill(0, func_num_args(), "%s"));
    return vsprintf($format, func_get_args());
  }
}
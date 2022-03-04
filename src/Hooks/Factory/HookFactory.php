<?php

namespace Dashifen\WPHandler\Hooks\Factory;

use Closure;
use Dashifen\WPHandler\Hooks\MethodHook;
use Dashifen\WPHandler\Hooks\ClosureHook;
use Dashifen\WPHandler\Hooks\HookException;
use Dashifen\WPHandler\Hooks\HookInterface;
use Dashifen\WPHandler\Handlers\HandlerInterface;

class HookFactory implements HookFactoryInterface
{
  /**
   * produceHook
   *
   * Returns an implementation of HookInterface to the calling scope.
   *
   * @param string           $hook
   * @param HandlerInterface $object
   * @param string|Closure   $callback
   * @param int              $priority
   * @param int              $argumentCount
   *
   * @return HookInterface
   * @throws HookException
   */
  public function produceHook(string $hook, HandlerInterface $object, $callback, int $priority = 10, int $argumentCount = 1): HookInterface
  {
    // the purpose of this function is to provide a single place where
    // HookInterface implementations are constructed.  our default factory
    // produces either MethodHooks or ClosureHooks based on the type of
    // $callback.  we don't type-check our $callback parameter to be sure
    // it's a Closure here because the ClosureHook will do that for us.
    
    return is_string($callback)
      ? new MethodHook($hook, $object, $callback, $priority, $argumentCount)
      : new ClosureHook($hook, $callback, $priority, $argumentCount);
  }
  
  /**
   * produceHookIndex
   *
   * Returns a string that can be used as an array index in the calling scope.
   *
   * @param string           $hook
   * @param HandlerInterface $object
   * @param string|Closure   $callback
   * @param int              $priority
   *
   * @return string
   */
  public function produceHookIndex(string $hook, HandlerInterface $object, $callback, int $priority): string
  {
    // in the past, we just made a string out of our parameters and called it
    // the hook's index.  now that we're allowing Closures as callbacks, we
    // we have to use spl_object_hash to create a string from the object.  then
    // we can produce an index with that string as if it were a method name.
    
    if ($callback instanceof Closure) {
      $callback = spl_object_hash($callback);
    }
    
    return sprintf('%s:%s:%s:%s', $hook, $object, $callback, $priority);
  }
}

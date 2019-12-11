<?php

namespace Dashifen\WPHandler\Hooks;

use Closure;
use Dashifen\WPHandler\Handlers\HandlerInterface;

/**
 * Interface HookInterface
 *
 * We wouldn't usually specify properties on an interface, but because the
 * HookCollectionInterface specifies that it returns implementations of this
 * interface, doing so helps some IDEs to know what properties we expect
 * those implementations to have.  Note:  not all implementations of this
 * interface will have all of these properties; e.g. the MethodHook doesn't
 * need a Closure.
 *
 * @property-read string           $hook
 * @property-read Closure          $callback
 * @property-read HandlerInterface $object
 * @property-read string           $method
 * @property-read int              $priority
 * @property-read int              $argumentCount
 *
 * @package Dashifen\WPHandler\Hooks
 */
interface HookInterface
{

}

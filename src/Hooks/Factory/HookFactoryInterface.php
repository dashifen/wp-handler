<?php

namespace Dashifen\WPHandler\Hooks\Factory;

use Dashifen\WPHandler\Hooks\HookException;
use Dashifen\WPHandler\Hooks\HookInterface;
use Dashifen\WPHandler\Handlers\HandlerInterface;

interface HookFactoryInterface
{
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
     */
    public function produceHook(string $hook, HandlerInterface $object, string $method, int $priority = 10, int $argumentCount = 1): HookInterface;
    
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
    public function produceHookIndex(string $hook, HandlerInterface $object, string $method, int $priority): string;
}

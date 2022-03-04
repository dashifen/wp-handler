<?php

namespace Dashifen\WPHandler\Hooks\Collection\Factory;

use Dashifen\WPHandler\Hooks\Collection\HookCollection;
use Dashifen\WPHandler\Hooks\Collection\HookCollectionInterface;

class HookCollectionFactory implements HookCollectionFactoryInterface
{
  /**
   * produceHookCollection
   *
   * So that Handlers and Agents can have different collections, we use a
   * factory to produce them rather than passing a single collection around
   * between all of them.
   *
   * @return HookCollectionInterface
   */
  public function produceHookCollection(): HookCollectionInterface
  {
    return new HookCollection();
  }
  
}

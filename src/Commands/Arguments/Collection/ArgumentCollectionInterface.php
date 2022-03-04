<?php

namespace Dashifen\WPHandler\Commands\Arguments\Collection;

use Dashifen\Collection\CollectionInterface;

interface ArgumentCollectionInterface extends CollectionInterface
{
  /**
   * getSynopsis
   *
   * When we actually want to load our command into the WP CLI, it's not quite
   * enough to simply return our collection as an array.  Instead we need to
   * slightly alter things so that the CLI gets its data the way it wants it.
   *
   * @return array
   */
  public function getSynopsis(): array;
}

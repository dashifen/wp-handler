<?php

namespace Dashifen\WPHandler\Commands\Collection;

use Dashifen\Collection\CollectionInterface;

interface CommandCollectionInterface extends CollectionInterface
{
  // this interface is used to identify CommandCollections for dependency
  // injection and little else.  therefore, we don't actually need to define
  // any additional interface methods here at this time.
}

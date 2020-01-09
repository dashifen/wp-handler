<?php

namespace Dashifen\WPHandler;

use Iterator;
use ArrayAccess;

interface CollectionInterface extends Iterator, ArrayAccess
{
    /**
     * getCollection
     *
     * Returns the entire collection.
     *
     * @return array
     */
    public function getCollection(): array;
    
    /**
     * resetCollection
     *
     * Resets the collection to an empty array.
     *
     * @return void
     */
    public function resetCollection(): void;
}

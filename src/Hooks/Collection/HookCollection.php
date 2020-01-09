<?php

namespace Dashifen\WPHandler\Hooks\Collection;

use Dashifen\WPHandler\Hooks\HookInterface;

/**
 * Class HookCollection
 *
 * This object is almost exactly the same as the AgentCollection except here we
 * can use type hinting and the instanceof operator to be sure that we've a
 * collection of HookInterface objects and not a more general collection of
 * other stuff.
 *
 * @package Dashifen\WPHandler\Hooks\Collection
 */
class HookCollection implements HookCollectionInterface
{
    /**
     * @var HookInterface[]
     */
    protected $collection = [];
    
    /**
     * getCollection
     *
     * Returns the entire collection of Hooks.
     *
     * @return HookInterface[]
     */
    public function getCollection(): array
    {
        return $this->collection;
    }
    
    /**
     * resetCollection
     *
     * Removes all Hooks from the collection.
     *
     * @return void
     */
    public function resetCollection(): void
    {
        $this->collection = [];
    }
    
    /**
     * current
     *
     * Return the current element
     *
     * @return HookInterface
     */
    public function current (): HookInterface
    {
        return current($this->collection);
    }
    
    /**
     * next
     *
     * Move forward to next element
     *
     * @return void
     */
    public function next (): void
    {
        next($this->collection);
    }
    
    /**
     * key
     *
     * Return the key of the current element
     *
     * @return string|null
     */
    public function key (): ?string
    {
        return key($this->collection);
    }
    
    /**
     * valid
     *
     * Checks if current position is valid
     *
     * @return boolean
     */
    public function valid (): bool
    {
        return is_string($this->key());
    }
    
    /**
     * rewind
     *
     * Rewind the Iterator to the first element
     *
     * @return void
     */
    public function rewind (): void
    {
        reset($this->collection);
    }
    
    /**
     * offsetExists
     *
     * Returns true if this offset exists.
     *
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists ($offset): bool
    {
        return isset($this->collection[$offset]);
    }
    
    /**
     * offsetGet
     *
     * Returns the HookInterface object at the given offset.
     *
     * @param mixed $offset
     *
     * @return HookInterface|null
     */
    public function offsetGet ($offset): ?HookInterface
    {
        return $this->collection[$offset] ?? null;
    }
    
    /**
     * offsetSet
     *
     * Adds the given value to our collection.  Because we can't alter the
     * method signature by adding type hints, we ensure that we receive an
     * HookInterface object within.
     *
     * @param string         $offset
     * @param HookInterface $value
     *
     *
     * @return void
     * @throws HookCollectionException
     */
    public function offsetSet ($offset, $value): void
    {
        if (!($value instanceof HookInterface)) {
            throw new HookCollectionException(
                'Values must be instances of objects implementing HookInterface',
                HookCollectionException::NOT_A_HOOK
            );
        }
        
        $this->collection[$offset] = $value;
    }
    
    /**
     * offsetUnset
     *
     * Resets the specified offset within our collection.
     *
     * @param mixed $offset
     *
     *
     * @return void
     */
    public function offsetUnset ($offset)
    {
        unset($this->collection[$offset]);
    }
}

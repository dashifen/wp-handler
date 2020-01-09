<?php

namespace Dashifen\WPHandler\Agents\Collection;

use Dashifen\WPHandler\Agents\AgentInterface;

/**
 * Class AgentCollection
 *
 * This object is almost exactly the same as the HookCollection except here we
 * can use type hinting and the instanceof operator to be sure that we've a
 * collection of AgentInterface objects and not a more general collection of
 * other stuff.
 *
 * @package Dashifen\WPHandler\Agents\Collection
 */
class AgentCollection implements AgentCollectionInterface
{
    /**
     * @var AgentInterface[]
     */
    protected $collection = [];
    
    /**
     * getCollection
     *
     * Returns the entire collection of agents.
     *
     * @return AgentInterface[]
     */
    public function getCollection (): array
    {
        return $this->collection;
    }
    
    /**
     * resetCollection
     *
     * Removes all Agents from the collection.
     *
     * @return void
     */
    public function resetCollection (): void
    {
        $this->collection = [];
    }
    
    /**
     * current
     *
     * Return the current element
     *
     * @return AgentInterface
     */
    public function current (): AgentInterface
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
     * Returns the AgentInterface object at the given offset.
     *
     * @param mixed $offset
     *
     * @return AgentInterface|null
     */
    public function offsetGet ($offset): ?AgentInterface
    {
        return $this->collection[$offset] ?? null;
    }
    
    /**
     * offsetSet
     *
     * Adds the given value to our collection.  Because we can't alter the
     * method signature by adding type hints, we ensure that we receive an
     * AgentInterface object within.
     *
     * @param string         $offset
     * @param AgentInterface $value
     *
     *
     * @return void
     * @throws AgentCollectionException
     */
    public function offsetSet ($offset, $value): void
    {
        if (!($value instanceof AgentInterface)) {
            throw new AgentCollectionException(
                'Values must be instances of objects implementing AgentInterface',
                AgentCollectionException::NOT_AN_AGENT
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

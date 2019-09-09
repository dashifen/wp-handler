<?php

namespace Dashifen\WPHandler\Agents\Collection;

use Iterator;
use ArrayAccess;
use Dashifen\WPHandler\Agents\AgentInterface;

/**
 * Class AgentCollection
 *
 * @package Dashifen\WPHandler\Agents\Collection
 */
class AgentCollection implements AgentCollectionInterface {
  /**
   * @var AgentInterface[]
   */
  protected $collection = [];

  /**
   * @var int
   */
  protected $keyPosition = 0;

  /**
   * @var array
   */
  protected $keys = [];

  /**
   * get
   *
   * Given a key, returns the Agent located at that key.  If the key is not
   * found within the collection, returns null.
   *
   * @param string $key
   *
   * @return AgentInterface|null
   */
  public function get (string $key): ?AgentInterface {
    return $this->collection[$key] ?? null;
  }

  /**
   * getAll
   *
   * Returns the entire collection of agents.
   *
   * @return AgentInterface[]
   */
  public function getAll (): array {
    return $this->collection;
  }

  /**
   * has
   *
   * Returns true if an Agent has been added at the specified key; false
   * otherwise.
   *
   * @param string $key
   *
   * @return bool
   */
  public function has (string $key): bool {
    return isset($this->collection[$key]);
  }

  /**
   * set
   *
   * Adds the Agent to the collection using the given key.  Will overwrite
   * prior Agents at the same key if flag is set.
   *
   * @param string        $key
   * @param AgentInterface $agent
   * @param bool          $overwrite
   *
   * @return void
   * @throws AgentCollectionException
   */
  public function set (string $key, AgentInterface $agent, bool $overwrite = false): void {
    if (!$overwrite && $this->has($key)) {

      // if we're not overwriting previously set hooks and this collection
      // has the specified key, then we'll throw an exception.  hopefully,
      // the calling scope will know what to do.

      throw new AgentCollectionException("Key exists: $key",
        AgentCollectionException::KEY_EXISTS);
    }

    $this->collection[$key] = $agent;
    $this->updateKeys();
  }

  /**
   * updateKeys
   *
   * Updates our internal record of the current keys in use within our
   * collection.  Called anytime we set or reset a key.
   *
   * @return void
   */
  private function updateKeys (): void {
    $this->keys = array_keys($this->collection);
  }

  /**
   * reset
   *
   * Resets (removes) the Agent at the specified key.
   *
   * @param string $key
   *
   * @return void
   */
  public function reset (string $key): void {
    unset($this->collection[$key]);
    $this->updateKeys();
  }

  /**
   * resetAll
   *
   * Removes all Agents from the collection.
   *
   * @return void
   */
  public function resetAll (): void {
    $this->collection = [];
    $this->updateKeys();
  }
}
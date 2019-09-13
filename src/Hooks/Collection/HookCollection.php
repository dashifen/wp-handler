<?php

namespace Dashifen\WPHandler\Hooks\Collection;

use Iterator;
use ArrayAccess;
use Dashifen\WPHandler\Hooks\HookInterface;

/**
 * Class HookCollection
 *
 * @package Dashifen\WPHandler\Hooks\Collection
 */
class HookCollection implements HookCollectionInterface {
  /**
   * @var array
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
   * Given a key, returns the HookInterface located at that key.  If the
   * key is not found within the collection, returns null.
   *
   * @param string $key
   *
   * @return HookInterface|null
   */
  public function get (string $key): ?HookInterface {
    return $this->collection[$key] ?? null;
  }

  /**
   * getAll
   *
   * Returns the entire collection of Hooks.
   *
   * @return HookInterface[]
   */
  public function getAll (): array {
    return $this->collection;
  }

  /**
   * has
   *
   * Returns true if a Hook has been added at the specified key; false
   * otherwise.
   *
   * @param string $key
   *
   * @return bool
   */
  public function has (string $key): bool {
    return array_key_exists($key, $this->collection);
  }

  /**
   * set
   *
   * Adds the Hook to the collection using the given key.  Will overwrite
   * prior Hooks at the same key if flag is set.
   *
   *
   * @param string        $key
   * @param HookInterface $hook
   * @param bool          $overwrite
   *
   * @return void
   * @throws HookCollectionException
   */
  public function set (string $key, HookInterface $hook, bool $overwrite = false): void {
    if (!$overwrite && $this->has($key)) {

      // if we're not overwriting previously set hooks and this collection
      // has the specified key, then we'll throw an exception.  hopefully,
      // the calling scope will know what to do.

      throw new HookCollectionException("Key exists: $key",
        HookCollectionException::KEY_EXISTS);
    }

    $this->collection[$key] = $hook;
    $this->updateKeys();
  }

  /**
   * updateKeys
   *
   * Updates the internal record of the keys that are in use within our
   * collection for use within the Iterable interface methods below.  Updated
   * whenever we set or reset a key.
   *
   * @return void
   */
  private function updateKeys (): void {
    $this->keys = array_keys($this->collection);
  }

  /**
   * reset
   *
   * Resets (removes) the Hook at the specified key.
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
   * Removes all Hooks from the collection.
   *
   * @return void
   */
  public function resetAll (): void {
    $this->collection = [];
    $this->updateKeys();
  }
}
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
class HookCollection implements HookCollectionInterface, ArrayAccess, Iterator {
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

  /**
   * Return the current element
   *
   * @link  https://php.net/manual/en/iterator.current.php
   * @return mixed Can return any type.
   * @since 5.0.0
   */
  public function current () {
    return $this->collection[$this->key()];
  }

  /**
   * Move forward to next element
   *
   * @link  https://php.net/manual/en/iterator.next.php
   * @return void Any returned value is ignored.
   * @since 5.0.0
   */
  public function next () {
    ++$this->keyPosition;
  }

  /**
   * Return the key of the current element
   *
   * @link  https://php.net/manual/en/iterator.key.php
   * @return mixed scalar on success, or null on failure.
   * @since 5.0.0
   */
  public function key () {
    return $this->keys[$this->keyPosition];
  }

  /**
   * Checks if current position is valid
   *
   * @link  https://php.net/manual/en/iterator.valid.php
   * @return boolean The return value will be casted to boolean and then evaluated.
   * Returns true on success or false on failure.
   * @since 5.0.0
   */
  public function valid () {
    return $this->has($this->key());
  }

  /**
   * Rewind the Iterator to the first element
   *
   * @link  https://php.net/manual/en/iterator.rewind.php
   * @return void Any returned value is ignored.
   * @since 5.0.0
   */
  public function rewind () {
    $this->keyPosition = 0;
  }

  /**
   * Whether a offset exists
   *
   * @link  https://php.net/manual/en/arrayaccess.offsetexists.php
   *
   * @param mixed $offset
   *
   * @return bool
   * @since 5.0.0
   */
  public function offsetExists ($offset): bool {
    return $this->has($offset);
  }

  /**
   * Offset to retrieve
   *
   * @link  https://php.net/manual/en/arrayaccess.offsetget.php
   *
   * @param mixed $offset
   *
   * @return HookInterface
   * @since 5.0.0
   */
  public function offsetGet ($offset): HookInterface {
    return $this->get($offset);
  }

  /**
   * Offset to set
   *
   * @link  https://php.net/manual/en/arrayaccess.offsetset.php
   *
   * @param mixed $offset
   * @param mixed $value
   *
   * @return void
   * @throws HookCollectionException
   * @since 5.0.0
   */
  public function offsetSet ($offset, $value) {
    $this->set($offset, $value);
  }

  /**
   * Offset to unset
   *
   * @link  https://php.net/manual/en/arrayaccess.offsetunset.php
   *
   * @param mixed $offset
   *
   * @return void
   * @since 5.0.0
   */
  public function offsetUnset ($offset) {
    $this->reset($offset);
  }
}
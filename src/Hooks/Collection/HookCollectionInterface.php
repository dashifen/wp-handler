<?php

namespace Dashifen\WPHandler\Hooks\Collection;

use Dashifen\WPHandler\Hooks\HookInterface;

interface HookCollectionInterface {
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
  public function get(string $key): ?HookInterface;

  /**
   * getAll
   *
   * Returns the entire collection of Hooks.
   *
   * @return HookInterface[]
   */
  public function getAll(): array;

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
  public function has(string $key): bool;

  /**
   * set
   *
   * Adds the Hook to the collection using the given key.  Will overwrite
   * prior Hooks at the same key if flag is set.  Returns true if a Hook is
   * added; false otherwise.
   *
   * @param string        $key
   * @param HookInterface $hook
   * @param bool          $overwrite
   *
   * @return void
   * @throws HookCollectionException
   */
  public function set(string $key, HookInterface $hook, bool $overwrite = false): void;

  /**
   * reset
   *
   * Resets (removes) the Hook at the specified key.
   *
   * @param string $key
   *
   * @return void
   */
  public function reset(string $key): void;

  /**
   * resetAll
   *
   * Removes all Hooks from the collection.
   *
   * @return void
   */
  public function resetAll (): void;
}
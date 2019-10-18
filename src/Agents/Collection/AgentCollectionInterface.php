<?php

namespace Dashifen\WPHandler\Agents\Collection;

use Dashifen\WPHandler\Agents\AgentInterface;

/**
 * Interface AgentCollectionInterface
 *
 * Every part of me wants to call this a Team of agents rather than a
 * Collection, but the latter is probably the better term for nerdy computer
 * science reasons.  Stupid computer scientists.
 *
 * @package Dashifen\WPHandler\Agents\Collection
 */
interface AgentCollectionInterface {
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
  public function get (string $key): ?AgentInterface;

  /**
   * getAll
   *
   * Returns the entire collection of agents.
   *
   * @return AgentInterface[]
   */
  public function getAll (): array;

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
  public function has (string $key): bool;

  /**
   * set
   *
   * Adds the Agent to the collection using the given key.  Will overwrite
   * prior Agents at the same key if flag is set.
   *
   * @param string         $key
   * @param AgentInterface $agent
   * @param bool           $overwrite
   *
   * @return void
   * @throws AgentCollectionException
   */
  public function set (string $key, AgentInterface $agent, bool $overwrite = false): void;

  /**
   * reset
   *
   * Resets (removes) the Agent at the specified key.
   *
   * @param string $key
   *
   * @return void
   */
  public function reset (string $key): void;

  /**
   * resetAll
   *
   * Removes all Agents from the collection.
   *
   * @return void
   */
  public function resetAll (): void;
}
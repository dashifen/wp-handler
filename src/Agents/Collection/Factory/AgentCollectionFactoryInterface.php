<?php

namespace Dashifen\WPHandler\Agents\Collection\Factory;

use Dashifen\WPHandler\Handlers\HandlerInterface;
use Dashifen\WPHandler\Agents\Collection\AgentCollection;
use Dashifen\WPHandler\Agents\Collection\AgentCollectionException;
use Dashifen\WPHandler\Repositories\AgentDefinition\AgentDefinition;

interface AgentCollectionFactoryInterface {
  /**
   * produceAgentCollection
   *
   * Returns an agent collection with agents linked to
   *
   * @param HandlerInterface $handler
   *
   * @return AgentCollection
   * @throws AgentCollectionException
   */
  public function produceAgentCollection(HandlerInterface $handler): AgentCollection;

  /**
   * registerAgent
   *
   * Given the fully namespaced object name for an Agent, stores it so that
   * we can produce a collection including it later.
   *
   * @param AgentDefinition $agent
   *
   * @return void
   */
  public function registerAgent(AgentDefinition $agent): void;

  /**
   * registerAgents
   *
   * Given an array of fully namespaced objects, stores them all for later
   * production as a collection.
   *
   * @param AgentDefinition[] $agents
   */
  public function registerAgents(array $agents): void;
}
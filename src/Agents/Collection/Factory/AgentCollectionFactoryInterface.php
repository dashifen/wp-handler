<?php

namespace Dashifen\WPHandler\Agents\Collection\Factory;

use Dashifen\Repository\RepositoryException;
use Dashifen\WPHandler\Handlers\HandlerInterface;
use Dashifen\WPHandler\Agents\Collection\AgentCollectionException;
use Dashifen\WPHandler\Agents\Collection\AgentCollectionInterface;
use Dashifen\WPHandler\Repositories\AgentDefinition\AgentDefinition;

interface AgentCollectionFactoryInterface
{
  /**
   * produceAgentCollection
   *
   * Returns an agent collection with agents linked to
   *
   * @param HandlerInterface $handler
   *
   * @return AgentCollectionInterface
   * @throws AgentCollectionException
   */
  public function produceAgentCollection(HandlerInterface $handler): AgentCollectionInterface;
  
  /**
   * registerAgent
   *
   * A convenience method that constructs an AgentDefinition based on this
   * method's parameters and then passes it to the registerAgentDefinition
   * method below.
   *
   * @param string $agent
   * @param array  ...$parameters
   *
   * @return void
   * @throws RepositoryException
   */
  public function registerAgent(string $agent, ...$parameters): void;
  
  /**
   * registerAgentDefinition
   *
   * Given the definition for an Agent, stores it so that we can produce a
   * collection including it later.
   *
   * @param AgentDefinition $agent
   *
   * @return void
   */
  public function registerAgentDefinition(AgentDefinition $agent): void;
  
  /**
   * registerAgentDefinitions
   *
   * Given an array of agent definitions, registers them.
   *
   * @param AgentDefinition[] $agents
   *
   * @return void
   */
  public function registerAgentDefinitions(array $agents): void;
}

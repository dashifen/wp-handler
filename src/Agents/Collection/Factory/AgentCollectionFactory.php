<?php

namespace Dashifen\WPHandler\Agents\Collection\Factory;

use Dashifen\WPHandler\Handlers\HandlerInterface;
use Dashifen\WPHandler\Agents\Collection\AgentCollection;
use Dashifen\WPHandler\Agents\Collection\AgentCollectionException;
use Dashifen\WPHandler\Repositories\AgentDefinition\AgentDefinition;

class AgentCollectionFactory implements AgentCollectionFactoryInterface {
  /**
   * @var AgentDefinition[]
   */
  protected $agentDefinitions = [];

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
  public function produceAgentCollection (HandlerInterface $handler): AgentCollection {
    $collection = new AgentCollection();

    foreach ($this->agentDefinitions as $agentDefinition) {

      // the first parameter sent to our agents' constructors must be their
      // handler.  so, before we instantiate anything, we want to add $handler
      // to the front of the array parameters in this definition.  then, we
      // use this local array to instantiate our objects.  finally, we add it
      // to our collection using the agent's name as its index to provide an
      // easy, O(1) lookup should we need to find it again later.

      $parameters = array_unshift($agentDefinition->parameters, $handler);
      $instance = new $agentDefinition->agent(...$parameters);
      $collection->set($agentDefinition->agent, $instance);
    }

    return $collection;
  }

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
  public function registerAgent (AgentDefinition $agent): void {
    $this->agentDefinitions[] = $agent;
  }

  /**
   * registerAgents
   *
   * Given an array of fully namespaced objects, stores them all for later
   * production as a collection.
   *
   * @param AgentDefinition[] $agents
   *
   * @return void
   */
  public function registerAgents (array $agents): void {

    // since we can't type hint the values within our parameter array, we
    // walk $agents and pass them to registerAgent() above.  then, its type
    // hint will throw a PHP error if someone passes something other than an
    // AgentDefinition here.

    array_walk($agents, [$this, 'registerAgent']);
  }
}
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

      // if our agent definition has an array of parameters, the setter for
      // that property requires that the first one be a Handler.  therefore, we
      // can construct our agent using those parameters and ignore $handler in
      // such cases.  otherwise, we can just use $handler as the single
      // argument to an agent's constructor.

      $instance = is_array($agentDefinition->parameters) && sizeof($agentDefinition->parameters) > 0
        ? new $agentDefinition->agent(...$agentDefinition->parameters)
        : new $agentDefinition->agent($handler);

      // we use the name of our agent as the index within our collection so
      // that we can find it again later in an O(1) lookup if we need to.

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
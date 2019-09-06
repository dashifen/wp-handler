<?php

namespace Dashifen\WPHandler\Agents\Collection\Factory;

use Dashifen\WPHandler\Agents\AbstractAgent;
use Dashifen\WPHandler\Handlers\HandlerInterface;
use Dashifen\WPHandler\Agents\Collection\AgentCollection;
use Dashifen\WPHandler\Agents\Collection\AgentCollectionException;

class AgentCollectionFactory implements AgentCollectionFactoryInterface {
  protected $agentRegistry = [];

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

    foreach ($this->agentRegistry as $i => $agent) {

      // we know that each $agent in our registry is a class name descended
      // from the AbstractAgent class because of our test in registerAgent
      // below.  each Agent needs a reference to its handler, so as we
      // construct them here to return them as a collection, we pass along
      // our HandlerInterface reference as well.

      $collection->set($i, new $agent($handler));
    }

    return $collection;
  }

  /**
   * registerAgent
   *
   * Given the fully namespaced object name for an Agent, stores it so that
   * we can produce a collection including it later.
   *
   * @param string $agent
   *
   * @return void
   * @throws AgentCollectionFactoryException
   */
  public function registerAgent (string $agent): void {
    if (!class_exists($agent) || !is_a($agent, AbstractAgent::class)) {
      $agentNameParts = explode("\\", $agent);

      throw new AgentCollectionFactoryException(
        sprintf("%s is not an agent", array_pop($agentNameParts)),
        AgentCollectionFactoryException::NOT_AN_AGENT
      );
    }

    $this->agentRegistry[] = $agent;
  }
}
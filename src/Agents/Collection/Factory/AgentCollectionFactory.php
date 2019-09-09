<?php

namespace Dashifen\WPHandler\Agents\Collection\Factory;

use Dashifen\WPHandler\Agents\AgentInterface;
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
      // from the AgentInterface class because of our test in registerAgent
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
    if (!class_exists($agent)) {
      throw new AgentCollectionFactoryException(
        sprintf("Unknown agent: %s", $this->getAgentShortName($agent)),
        AgentCollectionFactoryException::NOT_AN_AGENT
      );
    }

    $interfaces = class_implements($agent);
    if (!in_array(AgentInterface::class, $interfaces)) {
      throw new AgentCollectionFactoryException(
        sprintf("%s is not an agent", $this->getAgentShortName($agent)),
        AgentCollectionFactoryException::NOT_AN_AGENT
      );
    }

    $this->agentRegistry[] = $agent;
  }

  /**
   * getAgentShortName
   *
   * Given the fully namespaced version of a class name, returns only the
   * actual object's name, i.e. the class's short name.
   *
   * @param string $agentFullName
   *
   * @return string
   */
  private function getAgentShortName (string $agentFullName): string {
    $agentNameParts = explode("\\", $agentFullName);
    return array_pop($agentNameParts);
  }

  /**
   * registerAgents
   *
   * Given an array of fully namespaced objects, stores them all for later
   * production as a collection.
   *
   * @param array $agents
   * @throws AgentCollectionFactoryException
   */
  public function registerAgents (array $agents): void {

    // this one's easy:  we loop over our list of agents and pass them each
    // to the prior one and let it do all the work!

    foreach ($agents as $agent) {
      $this->registerAgent($agent);
    }
  }
}
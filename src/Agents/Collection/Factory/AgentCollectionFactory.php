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

    foreach ($this->agentRegistry as list($agent, $parameters)) {

      // we know that each $agent in our registry is a class name descended
      // from the AgentInterface class because of our test in registerAgent
      // below.  each Agent needs a reference to its handler, but how we give
      // them that reference is based on the $parameters variable.  if it's
      // not empty, we assume that our reference is in there already.
      // otherwise, we pass it directly.

      $instance = is_array($parameters) && sizeof($parameters) > 0
        ? new $agent(...$parameters)
        : new $agent($handler);

      $collection->set($agent, $instance);
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
   * @param array  $parameters
   *
   * @return void
   * @throws AgentCollectionFactoryException
   */
  public function registerAgent (string $agent, ...$parameters): void {
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

    // there are three abstract agent classes:  the AbstractAgent, the
    // AbstractPluginAgent, and the AbstractThemeAgent.  each extend the
    // Handler of a similar name, not each other.  this is because we need
    // the Handler's behaviors within each of them, i.e. plugin agents need
    // to know how plugin Handlers behave.  so, we'll get the parents of
    // our $agent and see if one of these three is within them.

    $temp = $agent;
    while ($temp = get_parent_class($temp)) {
      if (preg_match("/Abstract(?:Theme|Plugin)?Agent/", $temp)) {

        // if the name of this parent of our agent matches our regular
        // expression, then we're good to register it.  we do so simply by
        // adding it to our registry along with it's constructor parameters.

        $this->agentRegistry[] = [$agent, $parameters];
        return;
      }
    }

    // if we didn't return within our loop, that means none of our abstract
    // agent classes were parents of this one.  therefore, this one's not an
    // agent.  we've an exception for that...

    throw new AgentCollectionFactoryException(
      sprintf("%s is not an agent", $this->getAgentShortName($agent)),
      AgentCollectionFactoryException::NOT_AN_AGENT
    );
  }

  /**
   * getAgentShortName
   *
   * Given the fully namespaced name of an Agent, return it's short name,
   * i.e. it's class name without all the namespacing.
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
   *
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
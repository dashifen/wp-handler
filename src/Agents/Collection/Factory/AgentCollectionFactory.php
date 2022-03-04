<?php

namespace Dashifen\WPHandler\Agents\Collection\Factory;

use stdClass;
use Dashifen\Repository\RepositoryException;
use Dashifen\WPHandler\Handlers\HandlerInterface;
use Dashifen\WPHandler\Agents\Collection\AgentCollection;
use Dashifen\WPHandler\Agents\Collection\AgentCollectionInterface;
use Dashifen\WPHandler\Repositories\AgentDefinition\AgentDefinition;

class AgentCollectionFactory implements AgentCollectionFactoryInterface
{
  /**
   * @var AgentDefinition[]
   */
  protected array $agentDefinitions = [];
  
  /**
   * produceAgentCollection
   *
   * Returns an agent collection with agents linked to
   *
   * @param HandlerInterface $handler
   *
   * @return AgentCollectionInterface
   */
  public function produceAgentCollection(HandlerInterface $handler): AgentCollectionInterface
  {
    $collection = $this->produceAgentCollectionInstance();
    
    foreach ($this->agentDefinitions as $agentDefinition) {
      
      // an agent constructor's first parameter is that agent's handler.
      // but, for convenience, we let definitions skip that one if the
      // programmer wants to.  therefore, here we must check to see if
      // the agent definition has a reference to its handler and, if not,
      // use our handler parameter to add one.  just in case teh array of
      // this agent's parameters is empty, notice we use a stdClass
      // object and the null coalescing operator to avoid errors.
      
      $parameters = $agentDefinition->parameters;
      $firstParameter = $parameters[0] ?? new stdClass();
      
      if (!$this->isHandler($firstParameter)) {
        $parameters = $this->addHandler($parameters, $handler);
      }
      
      $instance = new $agentDefinition->agent(...$parameters);
      $collection[$agentDefinition->agent] = $instance;
    }
    
    return $collection;
  }
  
  /**
   * produceAgentCollectionInterface
   *
   * This method provides an easy way to override the default use of the
   * AgentCollection object herein.  Just extend this object and override
   * this method and you're good to go!
   *
   * @return AgentCollectionInterface
   */
  protected function produceAgentCollectionInstance(): AgentCollectionInterface
  {
    return new AgentCollection();
  }
  
  /**
   * isHandler
   *
   * Returns true if this object is a handler.
   *
   * @param object $maybeHandler
   *
   * @return bool
   */
  private function isHandler(object $maybeHandler): bool
  {
    // all handlers must, eventually, implement the HandlerInterface.  so,
    // if we can find it in our parameter's interfaces, we know that this
    // one is good to go.
    
    return in_array(HandlerInterface::class, class_implements($maybeHandler));
  }
  
  /**
   * addHandler
   *
   * Adds the HandlerInterface reference to the front of the parameters
   * array.
   *
   * @param array            $parameters
   * @param HandlerInterface $handler
   *
   * @return array
   */
  private function addHandler(array $parameters, HandlerInterface $handler): array
  {
    return array_merge([$handler], $parameters);
  }
  
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
  public function registerAgent(string $agent, ...$parameters): void
  {
    $agentDefinition = new AgentDefinition($agent, ...$parameters);
    $this->registerAgentDefinition($agentDefinition);
  }
  
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
  public function registerAgentDefinition(AgentDefinition $agent): void
  {
    $this->agentDefinitions[] = $agent;
  }
  
  /**
   * registerAgentDefinitions
   *
   * Given an array of agent definitions, registers them.
   *
   * @param AgentDefinition[] $agents
   *
   * @return void
   */
  public function registerAgentDefinitions(array $agents): void
  {
    // since we can't type hint the values within our parameter array, we
    // walk $agents and pass them to registerAgent() above.  then, its type
    // hint will throw a PHP error if someone passes something other than
    // an AgentDefinition here.
    
    array_walk($agents, [$this, 'registerAgentDefinition']);
  }
}

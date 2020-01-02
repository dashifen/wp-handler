<?php

namespace Dashifen\WPHandler\Agents\Collection\Factory;

use stdClass;
use Dashifen\Repository\RepositoryException;
use Dashifen\WPHandler\Handlers\HandlerInterface;
use Dashifen\WPHandler\Agents\Collection\AgentCollection;
use Dashifen\WPHandler\Agents\Collection\AgentCollectionException;
use Dashifen\WPHandler\Agents\Collection\AgentCollectionInterface;
use Dashifen\WPHandler\Repositories\AgentDefinition\AgentDefinition;

class AgentCollectionFactory implements AgentCollectionFactoryInterface
{
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
     * @return AgentCollectionInterface
     * @throws AgentCollectionException
     */
    public function produceAgentCollection (HandlerInterface $handler): AgentCollectionInterface
    {
        $collection = $this->produceAgentCollectionInstance();
        
        foreach ($this->agentDefinitions as $agentDefinition) {
            
            // the first parameter sent to our agents' constructors must be their
            // handler.  so, before we instantiate anything, we'll see if that's the
            // case.  if not, we'll add the reference we receive as the parameter to
            // this method.  this allows people to register an agent with or without
            // the handler reference based on their preference and we'll make sure it
            // all works out here.
            
            $parameters = $agentDefinition->parameters;
            $firstParam = $parameters[0] ?? new stdClass();
            if (!in_array(HandlerInterface::class, class_implements($firstParam))) {
                $parameters = array_merge([$handler], $parameters);
            }
            
            $instance = new $agentDefinition->agent(...$parameters);
            $collection->set($agentDefinition->agent, $instance);
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
    protected function produceAgentCollectionInstance (): AgentCollectionInterface
    {
        return new AgentCollection();
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
    public function registerAgent (string $agent, ...$parameters): void
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
    public function registerAgentDefinition (AgentDefinition $agent): void
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
    public function registerAgentDefinitions (array $agents): void
    {
        // since we can't type hint the values within our parameter array, we
        // walk $agents and pass them to registerAgent() above.  then, its type
        // hint will throw a PHP error if someone passes something other than an
        // AgentDefinition here.
        
        array_walk($agents, [$this, 'registerAgentDefinition']);
    }
}

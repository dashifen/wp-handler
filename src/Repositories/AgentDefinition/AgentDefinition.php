<?php

namespace Dashifen\WPHandler\Repositories\AgentDefinition;

use Dashifen\Repository\Repository;
use Dashifen\Repository\RepositoryException;
use Dashifen\WPHandler\Agents\AgentInterface;

/**
 * Class AgentDefinition
 *
 * @property-read string $agent
 * @property-read array  $parameters;
 *
 * @package Dashifen\WPHandler\Repositories
 */
class AgentDefinition extends Repository
{
  protected string $agent;
  protected array $parameters = [];
  
  /**
   * AgentDefinition constructor.
   *
   * @param string $agent
   * @param mixed  ...$parameters
   *
   * @throws RepositoryException
   */
  public function __construct(string $agent, ...$parameters)
  {
    parent::__construct(
      [
        'agent'      => $agent,
        'parameters' => $parameters,
      ]
    );
  }
  
  /**
   * setAgent
   *
   * Sets the agent property after confirming that the $agent parameter is the
   * name of an object that is, in fact, an Agent.
   *
   * @param string $agent
   *
   * @return void
   * @throws AgentDefinitionException
   */
  protected function setAgent(string $agent): void
  {
    // the string we receive here must be the name of an Agent.  so, before we
    // set our property, we want to confirm that this requirement is met.
    // first, we check to see if $agent references an existent class.
    
    if (!class_exists($agent)) {
      throw new AgentDefinitionException(
        sprintf("Unknown agent: %s", $this->getObjectShortName($agent)),
        AgentDefinitionException::NOT_AN_AGENT
      );
    }
    
    // now, we want to make sure that class implements the AgentInterface.
    
    $interfaces = class_implements($agent);
    if (!in_array(AgentInterface::class, $interfaces)) {
      throw new AgentDefinitionException(
        sprintf("%s is not an agent", $this->getObjectShortName($agent)),
        AgentDefinitionException::NOT_AN_AGENT
      );
    }
    
    // finally, each Agent must extend one of the following:  AbstractAgent,
    // AbstractPluginAgent, or AbstractThemeAgent.  we'll loop through the
    // parents of $agent and see if we find one of them.  if we do, we set our
    // property and return.  otherwise, we'll go through the entire loop and
    // then throw a final exception below.
    
    $temp = $agent;
    while ($temp = get_parent_class($temp)) {
      if (preg_match("/Abstract(?:Theme|Plugin)?Agent/", $temp)) {
        $this->agent = $agent;
        return;
      }
    }
    
    throw new AgentDefinitionException(
      sprintf("%s is not an agent", $this->getObjectShortName($agent)),
      AgentDefinitionException::NOT_AN_AGENT
    );
  }
  
  /**
   * getObjectShortName
   *
   * Given the fully namespaced name of an object, return it's short name,
   * i.e. it's class name without all the namespacing.
   *
   * @param string $objectFullName
   *
   * @return string
   */
  private function getObjectShortName(string $objectFullName): string
  {
    $agentNameParts = explode("\\", $objectFullName);
    return array_pop($agentNameParts);
  }
  
  /**
   * setParameters
   *
   * Sets the parameters property.
   *
   * @param array $parameters
   *
   * @return void
   */
  protected function setParameters(array $parameters): void
  {
    $this->parameters = $parameters;
  }
}

<?php

namespace Dashifen\WPHandler\Repositories\CommandDefinition;

use Dashifen\Repository\Repository;
use Dashifen\Repository\RepositoryException;
use Dashifen\WPHandler\Commands\CommandInterface;
use Dashifen\WPHandler\Commands\Arguments\Collection\ArgumentCollection;
use Dashifen\WPHandler\Commands\Arguments\Collection\ArgumentCollectionInterface;

/**
 * Class CommandDefinition
 *
 * @property-read string $command
 * @property-read string $argumentCollection
 * @property-read array  $parameters
 *
 * @package Dashifen\WPHandler\Repositories\CommandDefinition
 */
class CommandDefinition extends Repository
{
  protected string $command;
  protected string $argumentCollection;
  protected array $parameters = [];
  
  /**
   * CommandDefinition constructor.
   *
   * @param string      $command
   * @param string|null $argumentCollection
   * @param             ...$parameters
   *
   * @throws RepositoryException
   */
  public function __construct(string $command, string $argumentCollection = null, ...$parameters)
  {
    parent::__construct([
      'command'            => $command,
      'argumentCollection' => $argumentCollection ?? ArgumentCollection::class,
      'parameters'         => $parameters,
    ]);
  }
  
  /**
   * setCommand
   *
   * Sets the command property after confirming that our parameter names an
   * object that implements the CommandInterface and extends the
   * AbstractCommand.
   *
   * @param string $command
   *
   * @throws CommandDefinitionException
   */
  public function setCommand(string $command): void
  {
    // first, the command named by our parameter must be a class that exists.
    // then, we'll want to see if it implements the CommandInterface interface.
    
    if (!class_exists($command)) {
      throw new CommandDefinitionException(
        'Unknown command: ' . $this->getShortName($command),
        CommandDefinitionException::UNKNOWN_COMMAND
      );
    }
    
    $interfaces = class_implements($command);
    if (!in_array(CommandInterface::class, $interfaces)) {
      throw new CommandDefinitionException(
        $this->getShortName($command) . ' is not a Command',
        CommandDefinitionException::NOT_A_COMMAND
      );
    }
    
    // if both of those criteria are met, the next step is to ensure that our
    // command extends the AbstractCommand class.  if so, we're good to go.
    
    $temp = $command;
    while ($temp = get_parent_class($temp)) {
      if (strpos($temp, 'AbstractCommand') !== false) {
        $this->command = $command;
        
        // by returning here, we avoid throwing the exception below.
        
        return;
      }
    }
    
    // if we didn't return within the while loop above, then while the class
    // exists and implements our interface, it doesn't extend the abstract
    // command class.  that means we can't use it either and we'll throw one
    // more exception.
    
    throw new CommandDefinitionException(
      $this->getShortName($command) . ' must extend AbstractCommand',
      CommandDefinitionException::NOT_A_COMMAND
    );
  }
  
  /**
   * getCommandShortName
   *
   * Given the fully namespaced command name, returns just the CommandInterface
   * object's name that's at the end of it.
   *
   * @param string $className
   *
   * @return string
   */
  protected function getShortName(string $className): string
  {
    $classNameParts = explode('\\', $className);
    return array_pop($classNameParts);
  }
  
  /**
   * setArgumentCollection
   *
   * Sets the argument collection property after confirming that our parameter
   * names an object that implements the ArgumentCollectionInterface.
   *
   * @param string $argumentCollection
   *
   * @return void
   * @throws CommandDefinitionException
   */
  protected function setArgumentCollection(string $argumentCollection): void
  {
    // first, the command named by our parameter must be a class that exists.
    // then, we'll want to see if it implements the CommandInterface interface.
    // but, unlike our commands, there's no abstract object that we must
    // extend, so only those two checks happen here.
    
    if (!class_exists($argumentCollection)) {
      throw new CommandDefinitionException(
        'Unknown argument collection: ' . $this->getShortName($argumentCollection),
        CommandDefinitionException::UNKNOWN_ARGUMENT_COLLECTION
      );
    }
    
    $interfaces = class_implements($argumentCollection);
    if (!in_array(ArgumentCollectionInterface::class, $interfaces)) {
      throw new CommandDefinitionException(
        $this->getShortName($argumentCollection) . ' is not an ArgumentCollection',
        CommandDefinitionException::NOT_AN_ARGUMENT_COLLECTION
      );
    }
    
    $this->argumentCollection = $argumentCollection;
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

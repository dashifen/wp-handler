<?php

namespace Dashifen\WPHandler\Commands\Collection\Factory;

use stdClass;
use Dashifen\Repository\RepositoryException;
use Dashifen\WPHandler\Handlers\HandlerInterface;
use Dashifen\WPHandler\Commands\Collection\CommandCollection;
use Dashifen\WPHandler\Commands\Collection\CommandCollectionInterface;
use Dashifen\WPHandler\Repositories\CommandDefinition\CommandDefinition;
use Dashifen\WPHandler\Commands\Arguments\Collection\ArgumentCollection;
use Dashifen\WPHandler\Commands\Arguments\Collection\ArgumentCollectionInterface;

class CommandCollectionFactory implements CommandCollectionFactoryInterface
{
  /**
   * @var CommandDefinition[]
   */
  protected array $commandDefinitions = [];
  
  /**
   * produceCommandCollection
   *
   * Produces a CommandCollectionInterface object which contains each of the
   * commands registered with this Factory.
   *
   * @param HandlerInterface $handler
   *
   * @return CommandCollectionInterface
   */
  public function produceCommandCollection(HandlerInterface $handler): CommandCollectionInterface
  {
    $collection = $this->produceCommandCollectionInstance();
    
    foreach ($this->commandDefinitions as $definition) {
      
      // just like our Agents, the first parameter of a Command's constructor
      // is a handler.  unlike them, the second parameter must be the name of
      // an ArgumentCollectionInterface object.  so, we'll check on those two
      // requirements here and make sure we have what we need to construct our
      // command.
      
      $parameters = $definition->parameters;
      $maybeHandler = $parameters[0] ?? new stdClass();
      $maybeCollection = $parameters[1] ?? $definition->argumentCollection;
      $parameters = array_slice($parameters, 3);

      // for our collection, the $maybeCollection string is hopefully the
      // name of something that implements the ArgumentCollectionInterface. if
      // not, we can default to the ArgumentCollection object that is included
      // within this package.  otherwise, we instantiate the one specified in
      // the command's definition.
      
      $collection = !$this->hasInterface($maybeCollection, ArgumentCollectionInterface::class)
        ? new ArgumentCollection()
        : new $maybeCollection();
      
      // now, we want to add that collection to the front of our parameters.
      // this makes it the first one momentarily, but once we add our handler
      // it becomes the second which is right where it ought to be.
      
      array_unshift($parameters, $collection);
      
      // for our handler, either the $maybeHandler variable is already an
      // instance of a HandlerInterface object or we'll use the one passed here
      // as a parameter.  once we figure out which one to use, we'll add that
      // to the front of the array, too.
      
      $handler = $this->hasInterface($maybeHandler, HandlerInterface::class)
        ? $maybeHandler
        : $handler;
      
      array_unshift($parameters, $handler);
      
      // at this point, our parameters are a handler followed by an argument
      // collection, followed by any additional parameters specified by this
      // command's definition.  we'll run things through the array_values
      // function just to be sure that the indices are all numeric and then
      // construct our command.
      
      $command = new $definition->command(...$parameters);
      $collection[$definition->command] = $command;
    }
    
    return $collection;
  }
  
  /**
   * hasInterface
   *
   * Returns true if $object implements $interface.  note:  $object might be
   * an object or simply the class name of an object, so we can't type hint it
   * at this time.
   *
   * @param object|string $object
   * @param string        $interface
   *
   * @return bool
   */
  private function hasInterface($object, string $interface): bool
  {
    return is_array($interfaces = class_implements($object))
      && in_array($interface, $interfaces);
  }
  
  /**
   * produceCommandCollectionInstance
   *
   * In case someone wants to use a collection other than the default, this
   * method can be overridden by extensions to produce a different collection
   * object as long as it implements the CommandCollectionInterface, too.
   *
   * @return CommandCollectionInterface
   */
  private function produceCommandCollectionInstance(): CommandCollectionInterface
  {
    return new CommandCollection();
  }
  
  /**
   * registerCommand
   *
   * Prepares this Factory to include a command as a part of the collection it
   * produces
   *
   * @param string $command
   *
   * @return void
   * @throws RepositoryException
   */
  public function registerCommand(string $command): void
  {
    $definition = new CommandDefinition($command);
    $this->registerCommandDefinition($definition);
  }
  
  /**
   * registerCommandDefinition
   *
   * Registers a command definition within our factory so that it can produce
   * it within a collection when requested to do so.
   *
   * @param CommandDefinition $command
   */
  public function registerCommandDefinition(CommandDefinition $command): void
  {
    $this->commandDefinitions[] = $command;
  }
  
  /**
   * registerCommandDefinitions
   *
   * Given an array of command definitions, registers all of then at once.
   *
   * @param CommandDefinition[] $commands
   *
   * @return void
   */
  public function registerCommandDefinitions(array $commands): void
  {
    array_walk($commands, [$this, 'registerCommandDefinition']);
  }
}

<?php

namespace Dashifen\WPHandler\Traits;

use WP_CLI;
use Exception;
use ReflectionClass;
use Dashifen\WPHandler\Agents\AgentInterface;
use Dashifen\WPHandler\Commands\CommandInterface;
use Dashifen\WPHandler\Handlers\HandlerException;
use Dashifen\WPHandler\Handlers\HandlerInterface;
use Dashifen\CaseChangingTrait\CaseChangingTrait;
use Dashifen\WPHandler\Commands\Collection\CommandCollection;
use Dashifen\WPHandler\Commands\Collection\CommandCollectionInterface;

trait CommandLineTrait
{
  use CaseChangingTrait;
  
  protected CommandCollectionInterface $commands;
  
  protected function setCommandCollection(?CommandCollectionInterface $commands = null): void
  {
    $this->commands = $commands ?? new CommandCollection();
  }
  
  /**
   * registerCommand
   *
   * Adds a command agent to our collection of them.
   *
   * @param CommandInterface $command
   */
  protected function registerCommand(CommandInterface $command): void
  {
    if (!isset($this->commands)) {
      
      // if we try to register a command without setting up our collection of
      // them, then we'll set it up using the default CommandCollection object
      // if someone wants to use something else, they'll have to have told us
      // so before now.
      
      $this->setCommandCollection();
    }
    
    $this->commands[$command->slug] = $command;
  }
  
  /**
   * initializeCommands
   *
   * As a handler, this object already has an initialize method that its
   * extensions must implement.  Similar to the initializeAgents method, this
   * one is intended to add our commands to the WP CLI and should be called
   * from the aforementioned initialize method.
   *
   * @throws HandlerException
   */
  protected function initializeCommands(): void
  {
    // before we initialize our commands, we want to see if we have a namespace
    // for them.  we're assuming that it's most likely that this trait is used
    // by an Agent, and in such a case, we want the command namespace of that
    // agent's handler.  otherwise, if we are a handler, we can get our
    // namespace directly.  finally, if it's neither of those, we just default
    // to a lack of namespace.
    
    // TODO: make this a match statement in PHP 8
    
    if ($this instanceof AgentInterface) {
      $namespace = $this->getCommandNamespace($this->handler);
    } elseif ($this instanceof HandlerInterface) {
      $namespace = $this->getCommandNamespace($this);
    } else {
      $namespace = '';
    }
    
    foreach ($this->commands as $command) {
      /** @var CommandInterface $command */
      
      try {
        $command->initialize();
        WP_CLI::add_command(
          $namespace . $command->name,
          $command->getCallable(),
          $command->getDescription()
        );
      } catch (Exception $e) {
        throw new HandlerException($e->getMessage(), $e->getCode(), $e);
      }
    }
  }
  
  /**
   * getAgentNamespace
   *
   * Returns our commands' namespace based on information about the handler
   * instance passed here.
   *
   * @param HandlerInterface $handler
   *
   * @return string
   */
  private function getCommandNamespace(HandlerInterface $handler): string
  {
    // our namespace is either the SLUG constant as defined in the $handler
    // or the $handler's name itself.  to access the class's constants, we
    // grab a reflection of it.  then, we can check for a SLUG and if it's not
    // set, we fallback on the class's short name (i.e. the one without the
    // full namespace qualification).
    
    $reflection = new ReflectionClass($handler);
    $namespace = $reflection->getConstants()['SLUG']
      
      // PSR-1 requires that classes be named in PascalCase but the CLIs don't
      // tend to use it.  we'll convert our classname to kebab-case which will
      // work on the command line.  Thus, SuperGreatHandler would be converted
      // to super-great-handler.
      
      ?? $this->pascalToKebabCase($reflection->getShortName());
    
    // now, just in case we had an object name or slug that included capital
    // letters, we'll just pass it all through strtolower because none of the
    // core WP CLI commands use them.
    
    return strtolower($namespace);
  }
}

<?php

namespace Dashifen\WPHandler\Traits;

use WP_CLI;
use Exception;
use Dashifen\WPHandler\Commands\CommandInterface;
use Dashifen\WPHandler\Handlers\HandlerException;
use Dashifen\WPHandler\Commands\Collection\CommandCollection;
use Dashifen\WPHandler\Commands\Collection\CommandCollectionInterface;

trait CommandLineTrait
{
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
    foreach ($this->commands as $command) {
      /** @var CommandInterface $command */
      
      try {
        $command->initialize();
        WP_CLI::add_command(
          $command->name,
          $command->getCallable(),
          $command->getDescription()
        );
      } catch (Exception $e) {
        throw new HandlerException($e->getMessage(), $e->getCode(), $e);
      }
    }
  }
}

<?php

namespace Dashifen\WPHandler\Traits;

use WP_CLI;
use stdClass;
use Exception;
use Dashifen\WPHandler\Commands\CommandInterface;
use Dashifen\WPHandler\Handlers\HandlerException;
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
   * Registers the "top-level" command for the CLI; i.e. the information that
   * appears when you execute `wp help` on the command line.
   *
   * @param string      $name
   * @param string      $description
   * @param string|null $className
   *
   * @return void
   * @throws Exception
   */
  protected function registerCommand(string $name, string $description, ?string $className = null): void
  {
    // at this time, WP_CLI v2.5 requires that an object name be passed to the
    // add_command method if you're going to use subcommands.  any other means
    // of registering a top-level command results in a fatal error when you add
    // a subcommand.  since our top-level commands don't need to do anything,
    // i.e. they exist only to print the `wp help` information, we can use a
    // stdClass here just to make the rest of the system work better.  but, we
    // can specify a different object with the third parameter above if needed.
    
    $className = $className ?? stdClass::class;
    WP_CLI::add_command($name, $className, ['shortdesc' => $description]);
  }
  
  /**
   * registerCommand
   *
   * Adds a subcommand agent to our collection of them.
   *
   * @param CommandInterface $command
   *
   * @return void
   */
  protected function registerSubcommand(CommandInterface $command): void
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
          "$command->namespace $command->name",
          $command->getCallable(),
          $command->getDescription()
        );
      } catch (Exception $e) {
        throw new HandlerException($e->getMessage(), $e->getCode(), $e);
      }
    }
  }
}

<?php

namespace Dashifen\WPHandler\Commands\Collection\Factory;

use Dashifen\WPHandler\Handlers\HandlerInterface;
use Dashifen\WPHandler\Commands\Collection\CommandCollectionInterface;
use Dashifen\WPHandler\Repositories\CommandDefinition\CommandDefinition;

interface CommandCollectionFactoryInterface
{
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
  public function produceCommandCollection(HandlerInterface $handler): CommandCollectionInterface;
  
  /**
   * registerCommand
   *
   * Creates a CommandDefinition and then registers it.
   *
   * @param string $command
   */
  public function registerCommand(string $command): void;
  
  /**
   * registerCommandDefinition
   *
   * Registers a command definition within our factory so that it can produce
   * it within a collection when requested to do so.
   *
   * @param CommandDefinition $command
   */
  public function registerCommandDefinition(CommandDefinition $command): void;
  
  /**
   * registerCommandDefinitions
   *
   * Given an array of command definitions, registers all of then at once.
   *
   * @param CommandDefinition[] $commands
   *
   * @return void
   */
  public function registerCommandDefinitions(array $commands): void;
}

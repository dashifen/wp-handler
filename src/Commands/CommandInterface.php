<?php

namespace Dashifen\WPHandler\Commands;

use Closure;
use Dashifen\WPHandler\Agents\AgentInterface;
use Dashifen\WPHandler\Repositories\Arguments\ArgumentInterface;
use Dashifen\WPHandler\Commands\Arguments\Collection\ArgumentCollectionInterface;

/**
 * Interface AbstractCommandInterface
 *
 * @property string                      $name
 * @property string                      $slug
 * @property string                      $namespace
 * @property string                      $shortDesc
 * @property ?Closure                    $beforeInvoke
 * @property ?Closure                    $afterInvoke
 * @property string                      $longDesc
 * @property string                      $when
 * @property ArgumentCollectionInterface $arguments
 * @property bool                        $isDeferred
 *
 * @package Dashifen\WpHandler\Commands
 */
interface CommandInterface extends AgentInterface
{
  /**
   * addArgument
   *
   * Adds an argument synopsis to this command agent's argument collection.
   *
   * @param ArgumentInterface $argument
   *
   * @return void
   */
  public function addArgument(ArgumentInterface $argument): void;
  
  /**
   * getCallable
   *
   * Returns a callable function that is run at the time the CLI command is
   * executed to complete the work of the command.
   *
   * @return callable
   */
  public function getCallable(): callable;
  
  /**
   * getCommandDescription
   *
   * Returns the full description of the command this agent performs for use
   * as the third parameter to the WP_CLI add_command method.
   *
   * @return array
   */
  public function getDescription(): array;
}

<?php

namespace Dashifen\WPHandler\Handlers;

use Throwable;
use Dashifen\WPHandler\Hooks\Factory\HookFactoryInterface;
use Dashifen\WPHandler\Hooks\Collection\HookCollectionInterface;
use Dashifen\WPHandler\Agents\Collection\AgentCollectionInterface;
use Dashifen\WPHandler\Hooks\Collection\Factory\HookCollectionFactoryInterface;
use Dashifen\WPHandler\Agents\Collection\Factory\AgentCollectionFactoryInterface;

interface HandlerInterface
{
  /**
   * __call
   *
   * Magic methods are always a part of the interface, but this time we
   * need this one, so by declaring it here, PHP will throw a tantrum if
   * it's not defined.
   *
   * @param string $method
   * @param array  $arguments
   *
   * @return mixed
   */
  public function __call(string $method, array $arguments);
  
  /**
   * __toString
   *
   * Magic methods are always a part of the interface, but this time we
   * need this one, so by declaring it here, PHP will throw a tantrum if
   * it's not defined.
   *
   * @return string
   */
  public function __toString(): string;
  
  /**
   * initialize
   *
   * Uses protected addAction() and addFilter() to connect WordPress to
   * this object.
   *
   * @return void
   */
  public function initialize(): void;
  
  /**
   * getHookFactory
   *
   * Returns the hook factory property.
   *
   * @return HookFactoryInterface
   */
  public function getHookFactory(): HookFactoryInterface;
  
  /**
   * getHookCollection
   *
   * Returns the hook collection property.
   *
   * @return HookCollectionInterface
   */
  public function getHookCollection(): HookCollectionInterface;
  
  /**
   * getHookCollection
   *
   * Returns the hook collection factory property.
   *
   * @return HookCollectionFactoryInterface
   */
  public function getHookCollectionFactory(): HookCollectionFactoryInterface;
  
  /**
   * getAgentCollection
   *
   * In the unlikely event that we need to extract the agent collection from
   * this handler, this method returns the agent collection property.
   *
   * @return AgentCollectionInterface
   */
  public function getAgentCollection(): AgentCollectionInterface;
  
  /**
   * setAgentCollection
   *
   * Given an agent collection factory, produces an agent collection and
   * saves it in our properties.
   *
   * @param AgentCollectionFactoryInterface $agentCollectionFactory
   *
   * @return void
   */
  public function setAgentCollection(AgentCollectionFactoryInterface $agentCollectionFactory): void;
  
  /**
   * debug
   *
   * Given stuff, print information about it and then die() if the $die flag is
   * set.  Typically, this only works when the isDebug() method returns true,
   * but the $force parameter will override this behavior.
   *
   * @param mixed $stuff
   * @param bool  $die
   * @param bool  $force
   *
   * @return void
   */
  public static function debug($stuff, bool $die = false, bool $force = false): void;
  
  /**
   * writeLog
   *
   * Calling this method should write $data to the WordPress debug.log file.
   *
   * @param $data
   *
   * @return void
   */
  public static function writeLog($data): void;
  
  /**
   * isDebug
   *
   * Returns true when WP_DEBUG exists and is set.
   *
   * @return bool
   */
  public static function isDebug(): bool;
  
  /**
   * catcher
   *
   * This serves as a general-purpose Exception handler which displays
   * the caught object when we're debugging and writes it to the log when
   * we're not.
   *
   * @param Throwable $thrown
   *
   * @return void
   */
  public static function catcher(Throwable $thrown): void;
}

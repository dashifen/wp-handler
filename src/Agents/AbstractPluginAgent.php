<?php

namespace Dashifen\WPHandler\Agents;

use Dashifen\WPHandler\Handlers\HandlerException;
use Dashifen\WPHandler\Handlers\Plugins\AbstractPluginHandler;
use Dashifen\WPHandler\Handlers\Plugins\PluginHandlerInterface;

/**
 * Class AbstractPluginService
 *
 * The purpose of a Service is to provide a narrowly focused set of behaviors
 * that can be severed from the main Plugin code to help identify and separate
 * responsibilities.  They are, for all intents and purposes, simply other
 * Handlers, but these must be constructed with a link to the other Handler
 * that they serve.
 *
 * @package Dashifen\WPHandler\Handlers\Services
 */
abstract class AbstractPluginAgent extends AbstractPluginHandler implements AgentInterface
{
  protected PluginHandlerInterface $handler;
  
  /**
   * AbstractPluginService constructor.
   *
   * @param PluginHandlerInterface $handler
   *
   * @throws HandlerException
   */
  public function __construct(PluginHandlerInterface $handler)
  {
    // agents are meant to help their handler.  thus, we assume that any
    // agent that's in use by a handler uses the same type of hook object
    // as the handler.  in other words, we can get the handler's hook
    // factory and just use that here, too.  similarly, we assume that
    // they'll use the same type of hook collection, so we can pass the
    // handlers collection factory over to them as well.
    
    parent::__construct(
      $handler->getHookFactory(),
      $handler->getHookCollectionFactory()
    );
    
    $this->handler = $handler;
  }
  
  /**
   * getPluginDir
   *
   * Returns the path to the directory containing this Service's handler.
   *
   * @return string
   */
  public function getPluginDir(): string
  {
    return $this->handler->getPluginDir();
  }
  
  /**
   * getPluginUrl
   *
   * Returns the path to the URL for the directory containing this
   * Service's handler.
   *
   * @return string
   */
  public function getPluginUrl(): string
  {
    return $this->handler->getPluginUrl();
  }
}

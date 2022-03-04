<?php

namespace Dashifen\WPHandler\Agents;

use Dashifen\WPHandler\Handlers\AbstractHandler;
use Dashifen\WPHandler\Handlers\HandlerInterface;

/**
 * Class AbstractPluginService
 *
 * The purpose of a Service is to provide a narrowly focused set of behaviors
 * that can be severed from the main Theme or Plugin code to help identify
 * and separate responsibilities.  They are, for all intents and purposes,
 * simply other Handlers, but these must be constructed with a link to the
 * other Handler that they serve.
 *
 * @package Dashifen\WPHandler\Handlers\Services
 */
abstract class AbstractAgent extends AbstractHandler implements AgentInterface
{
  protected HandlerInterface $handler;
  
  /**
   * AbstractPluginService constructor.
   *
   * @param HandlerInterface $handler
   */
  public function __construct(HandlerInterface $handler)
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
}

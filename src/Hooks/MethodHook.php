<?php

namespace Dashifen\WPHandler\Hooks;

use Dashifen\Repository\RepositoryException;
use Dashifen\WPHandler\Handlers\HandlerInterface;

/**
 * Class MethodHook
 *
 * @property-read string           $method
 * @property-read HandlerInterface $handler
 *
 * @package Dashifen\WPHandler\Hooks
 */
class MethodHook extends AbstractHook
{
  protected string $method;
  protected HandlerInterface $handler;
  
  /**
   * MethodHook constructor.
   *
   * @param string           $hook
   * @param HandlerInterface $handler
   * @param string           $method
   * @param int              $priority
   * @param int              $argumentCount
   *
   * @throws HookException
   */
  public function __construct(string $hook, HandlerInterface $handler, string $method, int $priority = 10, int $argumentCount = 1)
  {
    try {
      parent::__construct(
        [
          'hook'          => $hook,
          'handler'       => $handler,
          'method'        => $method,
          'priority'      => $priority,
          'argumentCount' => $argumentCount,
        ]
      );
    } catch (RepositoryException $e) {
      // to avoid the calling scopes needing to know about this
      // RepositoryException, we're going to convert it to a HookException
      // which is more specific to this context.
      
      throw $this->convertException($e, HookException::FAILURE_TO_CONSTRUCT);
    }
  }
  
  /**
   * setMethod
   *
   * Sets the method property.
   *
   * @param string $method
   *
   * @return void
   */
  protected function setMethod(string $method): void
  {
    // we assume that the scope using this object has confirmed that this
    // method is a part of handler.  that's a bit more work than we would
    // usually ask of a Repository anyway.
    
    $this->method = $method;
  }
  
  /**
   * setHandler
   *
   * Sets the handler property.
   *
   * @param HandlerInterface $handler
   *
   * @return void
   */
  protected function setHandler(HandlerInterface $handler): void
  {
    $this->handler = $handler;
  }
}

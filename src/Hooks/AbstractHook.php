<?php

namespace Dashifen\WPHandler\Hooks;

use Dashifen\Repository\Repository;
use Dashifen\Repository\RepositoryException;

/**
 * Class AbstractHook
 *
 * @property-read string $hook
 * @property-read int    $priority
 * @property-read int    $argumentCount
 *
 * @package Dashifen\WPHandler\Hooks
 */
abstract class AbstractHook extends Repository implements HookInterface
{
  protected string $hook;
  protected int $priority = 10;
  protected int $argumentCount = 1;
  
  /**
   * convertException
   *
   * So calling scopes don't need to know about the RepositoryException because
   * of this object, we convert those into HookExceptions with this method.
   *
   * @param RepositoryException $exception
   * @param int                 $code
   *
   * @return HookException
   */
  protected function convertException(RepositoryException $exception, int $code)
  {
    return new HookException($exception->getMessage(), $code, $exception);
  }
  
  /**
   * @param string $hook
   */
  protected function setHook(string $hook): void
  {
    $this->hook = $hook;
  }
  
  /**
   * @param int $priority
   */
  protected function setPriority(int $priority = 10): void
  {
    $this->priority = $priority;
  }
  
  /**
   * @param int $argumentCount
   *
   * @throws HookException
   */
  protected function setArgumentCount(int $argumentCount = 1): void
  {
    if ($argumentCount < 0) {
      throw new HookException("Invalid argument count: $argumentCount.", HookException::INVALID_ARGUMENT_COUNT);
    }
    
    $this->argumentCount = $argumentCount;
  }
}

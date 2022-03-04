<?php

namespace Dashifen\WPHandler\Handlers;

use Dashifen\Exception\Exception;

/**
 * Class HandlerException
 *
 * @package Dashifen\WPHandler\Handlers
 */
class HandlerException extends Exception
{
  public const UNHOOKED_METHOD = 1;
  public const INAPPROPRIATE_CALL = 2;
  public const FAILURE_TO_HOOK = 3;
  public const UNKNOWN_OPTION = 4;
  public const OPTION_TOO_LONG = 5;
  public const INVALID_CALLBACK = 6;
  public const NOT_A_CHILD = 7;
}

<?php

namespace Dashifen\WPHandler\Hooks;

use Dashifen\Exception\Exception;

/**
 * Class HookException
 *
 * @package Dashifen\WPHandler\Hooks
 */
class HookException extends Exception
{
  public const FAILURE_TO_CONSTRUCT = 1;
  public const INVALID_ARGUMENT_COUNT = 2;
}

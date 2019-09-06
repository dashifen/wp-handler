<?php

namespace Dashifen\WPHandler\Hooks;

use Dashifen\Exception\Exception;

/**
 * Class HookException
 * @package Dashifen\WPHandler\Hooks
 */
class HookException extends Exception {
  public const INVALID_PRIORITY       = 1;
  public const INVALID_ARGUMENT_COUNT = 2;
  public const METHOD_NOT_FOUND       = 3;
  public const OBJECT_NOT_FOUND       = 4;
}
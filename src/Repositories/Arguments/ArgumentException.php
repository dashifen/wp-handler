<?php

namespace Dashifen\WPHandler\Repositories\Arguments;

use Dashifen\Exception\Exception;

class ArgumentException extends Exception
{
  public const INVALID_TYPE = 1;
  public const INVALID_DEFAULT = 2;
  public const ACCESS_VIOLATION = 3;
}

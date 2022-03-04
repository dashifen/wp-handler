<?php

namespace Dashifen\WPHandler\Commands;

use Dashifen\Exception\Exception;

class CommandException extends Exception
{
  public const INVALID_VALUE = 1;
  public const UNKNOWN_PROPERTY = 2;
}

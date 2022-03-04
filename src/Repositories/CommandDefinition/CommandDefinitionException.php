<?php

namespace Dashifen\WPHandler\Repositories\CommandDefinition;

use Dashifen\Exception\Exception;

class CommandDefinitionException extends Exception
{
  public const UNKNOWN_COMMAND = 1;
  public const UNKNOWN_ARGUMENT_COLLECTION = 2;
  public const NOT_A_COMMAND = 3;
  public const NOT_AN_ARGUMENT_COLLECTION = 4;
}

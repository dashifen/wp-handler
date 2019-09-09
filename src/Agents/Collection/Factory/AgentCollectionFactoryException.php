<?php

namespace Dashifen\WPHandler\Agents\Collection\Factory;

use Dashifen\Exception\Exception;

class AgentCollectionFactoryException extends Exception {
  public const NOT_AN_AGENT  = 1;
  public const UNKNOWN_AGENT = 2;
}
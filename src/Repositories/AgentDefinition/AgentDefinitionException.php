<?php

namespace Dashifen\WPHandler\Repositories\AgentDefinition;

use Dashifen\Repository\RepositoryException;

class AgentDefinitionException extends RepositoryException {
  public const NOT_AN_AGENT  = 1;
  public const NOT_A_HANDLER = 2;
}
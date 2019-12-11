<?php

namespace Dashifen\WPHandler\Hooks\Collection;

use Dashifen\Exception\Exception;

class HookCollectionException extends Exception
{
    public const KEY_EXISTS = 1;
}

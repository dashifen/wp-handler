<?php

namespace Dashifen\WPHandler\Hooks;

use Dashifen\Exception\Exception;

/**
 * Class HookException
 * @package Dashifen\WPHandler\Hooks
 */
class HookException extends Exception {
	const INVALID_PRIORITY = 1;
	const INVALID_ARGUMENT_COUNT = 2;
	const METHOD_NOT_FOUND = 3;
	const OBJECT_NOT_FOUND = 4;
}
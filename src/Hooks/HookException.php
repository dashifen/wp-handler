<?php

namespace Engage\WordPress\Hooks;

use Dashifen\Exception\Exception;

/**
 * Class HookException
 * @package Engage\WordPress\Hooks
 */
class HookException extends Exception {
	const INVALID_PRIORITY = 1;
	const INVALID_ARGUMENT_COUNT = 2;
	const METHOD_NOT_FOUND = 3;
	const OBJECT_NOT_FOUND = 4;
}
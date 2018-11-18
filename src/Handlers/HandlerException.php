<?php

namespace Engage\WordPress\Handlers;

use Dashifen\Exception\Exception;

/**
 * Class HandlerException
 * @package Engage\WordPress\Handlers
 */
class HandlerException extends Exception {
	const UNHOOKED_METHOD = 1;
	const INAPPROPRIATE_CALL = 2;
}
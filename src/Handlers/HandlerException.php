<?php

namespace Dashifen\WPHandler\Handlers;

use Dashifen\Exception\Exception;

/**
 * Class HandlerException
 * @package Dashifen\WPHandler\Handlers
 */
class HandlerException extends Exception {
	const UNHOOKED_METHOD = 1;
	const INAPPROPRIATE_CALL = 2;
}
<?php

namespace Dashifen\WPHandler\Pages;

use Dashifen\Exception\Exception;

/**
 * Class PageException
 * @package Dashifen\WPHandler\Pages
 */
class PageException extends Exception {
	const CANNOT_RENDER_TEMPLATE = 1;
	const TEMPLATE_LOCATION_NOT_FOUND = 2;
}
<?php

namespace Engage\WordPress\Pages;

/**
 * Interface PageInterface
 * @package Engage\WordPress\Pages
 */
interface PageInterface {
	/**
	 * show
	 *
	 * Given a template, displays it using the $context property for this
	 * page.  If $debug is set, then it also prints the $context property in
	 * a large comment at the top of the page.
	 *
	 * @param string $template
	 * @param bool   $debug
	 *
	 * @return mixed
	 */
	public function show(string $template, bool $debug = false);
}
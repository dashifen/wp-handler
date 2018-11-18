<?php

namespace Engage\WordPress\Pages;

/**
 * Class Page
 * @package Engage\WordPress\Pages
 */
class Page implements PageInterface {
	/**
	 * show
	 *
	 * Given a template, this method displays it.
	 *
	 * @param string $template
	 * @param bool   $debug
	 *
	 * @return void
	 */
	public function show(string $template, bool $debug = false) {
		if ($debug) {
			echo "<!-- debugging -->";
		}

		// okay, so as methods go, this one isn't that exciting.  it
		// really starts to shine when we're using some form of templating
		// system, like Twig/Timber or Handlebars or whatever.  but, for now,
		// we just echo our $template and we're good to go.

		echo $template;
	}
}
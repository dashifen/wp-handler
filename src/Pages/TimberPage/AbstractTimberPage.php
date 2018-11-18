<?php

/** @noinspection PhpUndefinedNamespaceInspection */
/** @noinspection PhpUndefinedFunctionInspection */
/** @noinspection PhpUndefinedClassInspection */

namespace Engage\WordPress\Pages\TimberPage;

use Engage\WordPress\Pages\Page;
use Engage\WordPress\Pages\PageException;
use Timber\Timber;

/**
 * Class AbstractTimberPage
 * @package Engage\WordPress\Pages\TimberPage
 */
abstract class AbstractTimberPage extends Page {
	/**
	 * The data context for a Twig template.  Frequently initialized by
	 * the Timber::get_context() function.
	 *
	 * @var array
	 */
	protected $context = [];

	/**
	 * AbstractTimberPage constructor.
	 *
	 * @param bool $getTimberContext
	 */
	public function __construct(bool $getTimberContext = false) {
		if($getTimberContext) {
			$this->context = Timber::get_context();
		}

		$this->addToThisPageContext();
		$this->addToAllPagesContext();
	}

	/**
	 * addToThisPageContext
	 *
	 * Adds data to the $context property that is, later, used to render
	 * a Twig template.
	 *
	 * @return void
	 */
	abstract protected function addToThisPageContext();

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
	 * @return void
	 * @throws PageException
	 */
	public function show(string $template, bool $debug = false) {
		if ($debug) {
			echo "<!-- " . print_r($this->context, true) . " -->";
		}

		if (empty($template)) {
			throw new PageException("Cannot render without template",
				PageException::CANNOT_RENDER_TEMPLATE);
		}

		Timber::render($template, $this->context);
	}

	/**
	 * addToAllPagesContext
	 *
	 * By default, simply adds a siteUrl index to our context.  Called
	 * from the constructor; likely to be overwritten by children.
	 *
	 * @return void
	 */
	protected function addToAllPagesContext() {
		$this->context["siteUrl"] = get_site_url();
	}
}
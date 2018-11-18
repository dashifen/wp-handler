<?php

/** @noinspection PhpUndefinedNamespaceInspection */
/** @noinspection PhpUndefinedFunctionInspection */
/** @noinspection PhpUndefinedClassInspection */

namespace Dashifen\WPHandler\Pages\TimberPage;

use Dashifen\WPHandler\Pages\Page;
use Dashifen\WPHandler\Pages\PageException;
use Timber\Timber;

/**
 * Class AbstractTimberPage
 * @package Dashifen\WPHandler\Pages\TimberPage
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
	 * The ID for a singular post.
	 *
	 * @var int
	 */
	protected $postId = 0;

	/**
	 * AbstractTimberPage constructor.
	 *
	 * @param bool $getTimberContext
	 */
	public function __construct(bool $getTimberContext = false) {
		if($getTimberContext) {
			$this->context = Timber::get_context();
		}

		$this->postId = is_singular() ? get_the_ID() : 0;
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
	 * getContext
	 *
	 * Returns the context property of this object.
	 *
	 * @return array
	 */
	public function getContext(): array {
		return $this->context;
	}

	/**
	 * getContextValue
	 *
	 * Uses the $index parameter to drill down into the context property
	 * and returns a specific value within it.  $index should be a space
	 * separated "path" to the index we need.  so, if we wanted to return
	 * $this->context["foo"]["bar"], $index should be "foo bar."
	 *
	 * @param string $index
	 *
	 * @return mixed|null
	 */
	public function getContextValue(string $index) {

		// to drill down into our context property, we start from the
		// assumption that we're returning the whole thing.  then, we
		// explode the $index we're given on spaces and filter out any
		// blanks.  these $indices we use in a loop to dive into our
		// context to find the value that was requested.

		$retValue = $this->context;
		$indices = array_filter(explode(" ", $index));
		foreach ($indices as $index) {

			// this is where we drill down.  we assume each $index can be
			// found in our $retValue.  each iteration then "moves" us
			// through the dimensions of our context property.  if we ever
			// find an $index that is not available, we return null to tell
			// the calling scope that it messed up its request.

			$retValue = $retValue[$index] ?? null;

			if (is_null($retValue)) {
				return null;
			}
		}

		return $retValue;
	}


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
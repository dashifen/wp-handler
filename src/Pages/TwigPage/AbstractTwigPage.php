<?php

/** @noinspection PhpUndefinedMethodInspection */
/** @noinspection PhpUndefinedFunctionInspection */
/** @noinspection PhpUndefinedClassInspection */

namespace Engage\WordPress\Pages\TwigPage;

use Engage\WordPress\Pages\Page;
use Engage\WordPress\Pages\PageException;
use Twig_Loader_Filesystem;
use Twig_Environment;

abstract class AbstractTwigPage extends Page implements TwigPageInterface {
	/**
	 * The data context for a Twig template.
	 *
	 * @var array
	 */
	protected $context = [];

	/**
	 * @var string
	 */
	protected $templatePath;

	/**
	 * @var Twig_Environment
	 */
	protected $environment;

	/**
	 * AbstractTimberPage constructor.
	 */
	public function __construct() {
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
	 * setTemplateLocation
	 *
	 * Given a path to a directory, stores it as the filesystem location
	 * where we can find our templates.
	 *
	 * @param string $pathToTemplates
	 *
	 * @return void

	 * @throws PageException
	 */
	public function setTemplateLocation(string $pathToTemplates) {
		if (!is_dir($pathToTemplates)) {
			throw new PageException("Templates not found: $pathToTemplates.",
				PageException::TEMPLATE_LOCATION_NOT_FOUND);
		}

		$this->templatePath = $pathToTemplates;
	}

	/**
	 * setEnvironment
	 *
	 * Given a Twig_Environment, use it as the renderer for our templates.
	 *
	 * @param Twig_Environment|null $environment
	 *
	 * @return void
	 * @throws PageException
	 */
	public function setEnvironment(Twig_Environment $environment = null) {
		if (!is_null($environment)) {

			// if we were sent an environment, we use it.

			$this->environment = $environment;
		} else {

			// otherwise, we construct an environment from the template path.
			// which, if we don't have that either, then we'll only be able to
			// throw a tantrum.

			if (empty($this->templatePath)) {
				throw new PageException("Template path empty.",
					PageException::TEMPLATE_LOCATION_NOT_FOUND);
			}

			$loader = new Twig_Loader_Filesystem($this->templatePath);
			$this->environment = new Twig_Environment($loader);
		}
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

		if (is_null($this->environment)) {
			$this->setEnvironment();
		}

		echo $this->environment->render($template, $this->context);
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
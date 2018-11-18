<?php

/** @noinspection PhpUndefinedClassInspection */

namespace Engage\WordPress\Pages\TwigPage;

use Twig_Environment;

interface TwigPageInterface {
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
	public function setTemplateLocation(string $pathToTemplates);

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
	public function setEnvironment(Twig_Environment $environment = null);
}
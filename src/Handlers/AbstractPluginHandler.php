<?php

namespace Dashifen\WPHandler\Handlers;

abstract class AbstractPluginHandler extends AbstractHandler {
	/**
	 * @var string
	 */
	protected $pluginDir = "";

	/**
	 * @var string
	 */
	protected $pluginUrl = "";

	public function __construct() {
		parent::__construct();

		$this->pluginDir = WP_PLUGIN_DIR . "/" . $this->getPluginDirectory();
		$this->pluginUrl = WP_PLUGIN_URL . "/" . $this->getPluginDirectory();
	}

	/**
	 * getPluginDirectory
	 *
	 * Returns the name of the directory in which our concrete extension
	 * of this class resides.  Avoids the use of a ReflectionClass simply
	 * to get a simple string.
	 *
	 * @return string
	 */
	abstract protected function getPluginDirectory(): string;

	/**
	 * enqueue
	 *
	 * Adds a script or style to the DOM.
	 *
	 * @param string           $file
	 * @param array            $dependencies
	 * @param string|bool|null $finalArg
	 * @param string           $url
	 * @param string           $dir
	 *
	 * @return void
	 */
	protected function enqueue(string $file, array $dependencies = [], $finalArg = null, string $url = "", string $dir = ""): void {

		// our parent's enqueue function enqueues things that are in the
		// stylesheet's directory.  but that won't work for plugins.  we'll
		// set different defaults for our url and dir parameters and then
		// pass them to our parent's function as follows.

		if (empty($url)) {
			$url = $this->pluginUrl;
		}

		if (empty($dir)) {
			$dir = $this->pluginDir;
		}

		parent::enqueue($file, $dependencies, $finalArg, $url, $dir);
	}
}
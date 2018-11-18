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
}
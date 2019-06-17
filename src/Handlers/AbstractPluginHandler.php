<?php

namespace Dashifen\WPHandler\Handlers;

use ReflectionClass;
use ReflectionException;

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

		$pluginUrl = WP_PLUGIN_URL . "/" . $this->getPluginDirectory();
		$this->pluginUrl = preg_replace("/^https?:/", "", $pluginUrl);
		$this->pluginDir = WP_PLUGIN_DIR . "/" . $this->getPluginDirectory();
	}

  /**
   * getPluginDirectory
   *
   * Returns the name of the directory in which our concrete extension
   * of this class resides.
   *
   * @return string
   */
	final protected function getPluginDirectory(): string {
	  try {

	    // to get the directory name of this object's children we need to
      // reflect the static::class name of that child.  then, we can get
      // it's directory from it's full, absolute filename.  ordinarily,
      // we might want to avoid using a ReflectionClass since they can be
      // expensive, but because the object is already in memory, it's
      // much less so.

	    $classInfo = new ReflectionClass(static::class);
	    $absPath = dirname($classInfo->getFileName());

	    // now, all we really want is the final directory in the path.
      // that'll be the one in which our plugin lives, and keeping all
      // of the other information would actually cause our links to
      // fail.

      $absPathParts = explode(DIRECTORY_SEPARATOR, $absPath);
      $directory = array_pop($absPathParts);
    } catch (ReflectionException $exception) {

      // a ReflectionException is thrown when the class that we're
      // trying to reflect doesn't exist.  but, since we're reflecting
      // this class, we know it exists.  in order to avoid IDE related
      // messages about uncaught exceptions, we'll trigger the following
      // error, but we also know that we should never get here.

      trigger_error("Unable to reflect.", E_ERROR);
    }

    // the trigger_error() call in the catch block would halt our execution
    // of this method, but IDEs may flag the following line as a problem
    // because $directory would only exist if the exception isn't thrown.
    // thus, we'll use the null coalescing operator to make them happy.

    return $directory ?? "";
  }

	/**
	 * enqueue
	 *
	 * Adds a script or style to the DOM and returns the name by which
	 * the file is now known to WordPress
	 *
	 * @param string           $file
	 * @param array            $dependencies
	 * @param string|bool|null $finalArg
	 * @param string           $url
	 * @param string           $dir
	 *
	 * @return string
	 */
	protected function enqueue(string $file, array $dependencies = [], $finalArg = null, string $url = "", string $dir = ""): string {

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

		return parent::enqueue($file, $dependencies, $finalArg, $url, $dir);
	}
}

<?php

namespace Dashifen\WPHandler\Handlers\Themes;

use Dashifen\WPHandler\Handlers\AbstractHandler;
use Dashifen\WPHandler\Hooks\Factory\HookFactoryInterface;
use Dashifen\WPHandler\Hooks\Collection\Factory\HookCollectionFactoryInterface;

/**
 * Class AbstractHandler
 *
 * This object defines a common set of methods for WordPress themes that
 * allow a very simple public interface and access to protected methods as
 * WordPress hooks preventing accidental/malicious execution of a theme's
 * methods elsewhere.
 *
 * @package Dashifen\WPHandler\Handlers\Themes
 */
abstract class AbstractThemeHandler extends AbstractHandler implements ThemeHandlerInterface {
  /**
   * @var string
   */
  protected $stylesheetDir;

  /**
   * @var string
   */
  protected $stylesheetUrl;

  /**
   * AbstractHandler constructor.
   *
<<<<<<< HEAD
   * @param HookFactoryInterface           $hookFactory
   * @param HookCollectionFactoryInterface $hookCollectionFactory
=======
   * @param HookFactoryInterface                 $hookFactory
   * @param HookCollectionFactoryInterface       $hookCollectionFactory
>>>>>>> bd085af7a0e01c7b61612fcc2f27e8406bcc467b
   */
  public function __construct (
    HookFactoryInterface $hookFactory,
    HookCollectionFactoryInterface $hookCollectionFactory
  ) {
    parent::__construct($hookFactory, $hookCollectionFactory);

    // in case we're loading a minimalist WP environment for testing
    // purposes, we only want to set these properties if the functions
    // necessary for doing so exist.  95% of the time, they do, but in
    // the 5% where they don't, we'd have fatal errors.

    if (function_exists("get_stylesheet_directory_uri")) {
      $this->stylesheetUrl = get_stylesheet_directory_uri();
      $this->stylesheetDir = get_stylesheet_directory();
    }
  }

  /**
   * getUrl
   *
   * Returns the URL that corresponds to the folder in which this Handler
   * is located.
   *
   * @return string
   */
  public function getStylesheetUrl (): string {
    return $this->stylesheetUrl;
  }

  /**
   * getDir
   *
   * Returns the filesystem path to the folder in which this Handler is
   * located.
   *
   * @return string
   */
  public function getStylesheetDir (): string {
    return $this->stylesheetDir;
  }

  /**
   * enqueue
   *
   * Adds a script or style to the DOM and returns the name by which
   * the file is now known to WordPress.  This method is protected, things
   * from outside the scope of our theme shouldn't be messing with our
   * assets, so it doesn't need to be in our interface.
   *
   * @param string           $file
   * @param array            $dependencies
   * @param string|bool|null $finalArg
   * @param string           $url
   * @param string           $dir
   *
   * @return string
   */
  protected function enqueue (string $file, array $dependencies = [], $finalArg = null, string $url = "", string $dir = ""): string {
    $fileInfo = pathinfo($file);
    $isScript = ($fileInfo["extension"] ?? "") === "js";

    // if either of our url or dir parameters are empty, we'll want to
    // set them to the url and dir properties of this object.  this is the
    // default behavior, but our AbstractPluginHandler sends us other
    // strings.  then, we make sure that each of these has a trailing
    // slash.

    if (empty($url)) {
      $url = $this->stylesheetUrl;
    }

    if (empty($dir)) {
      $dir = $this->stylesheetDir;
    }

    $url = trailingslashit($url);
    $dir = trailingslashit($dir);

    // the $function variable will be used as a variable function.  we
    // want to set it to either the WP function that enqueues scripts or
    // the one for styles.  then, we can call that function below using
    // $function().

    $function = $isScript ? "wp_enqueue_script" : "wp_enqueue_style";

    if (is_null($finalArg)) {

      // the final argument for our $function is either a Boolean or
      // a string for scripts and styles respectively.  if it's null
      // at the moment, we'll default it to the following.  otherwise,
      // we assume the calling scope knows what it's doing.

      $finalArg = $isScript ? true : "all";
    }

    // if the asset we're enqueuing begins with "//" then it's a remote
    // asset.  we don't want to prefix it with our local URL and DIR
    // values.  first, we replace the protocol designation just to be
    // sure it's not present for our test.

    $file = preg_replace("/^https?:/", "", $file);
    $isRemote = substr($file, 0, 2) === "//";

    // the include is either the $file itself or that prefixed by our
    // URL property.  but, for the version of that file, we use the last
    // modified timestamp for local files and this year and month for
    // remote ones.  that should force browsers to update their cache
    // at least once per month for remote assets.

    $include = !$isRemote ? ($url . $file) : $file;
    $version = !$isRemote ? filemtime($dir . $file) : date("Ym");
    $function($fileInfo["filename"], $include, $dependencies, $version, $finalArg);

    return $fileInfo["filename"];
  }
}

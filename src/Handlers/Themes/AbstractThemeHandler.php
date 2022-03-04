<?php

namespace Dashifen\WPHandler\Handlers\Themes;

use WP_Theme;
use Dashifen\WPHandler\Handlers\AbstractHandler;
use Dashifen\WPHandler\Handlers\HandlerException;
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
abstract class AbstractThemeHandler extends AbstractHandler implements ThemeHandlerInterface
{
  protected string $stylesheetDir;
  protected string $stylesheetUrl;
  protected WP_Theme $themeData;
  
  /**
   * AbstractHandler constructor.
   *
   * @param HookFactoryInterface|null           $hookFactory
   * @param HookCollectionFactoryInterface|null $hookCollectionFactory
   */
  public function __construct(
    ?HookFactoryInterface $hookFactory = null,
    ?HookCollectionFactoryInterface $hookCollectionFactory = null
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
  public function getStylesheetUrl(): string
  {
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
  public function getStylesheetDir(): string
  {
    return $this->stylesheetDir;
  }
  
  /**
   * getThemeData
   *
   * Returns information about this theme as per the information retrievable
   * by the wp_get_theme stuff.
   *
   * @param string $datum
   * @param string $default
   *
   * @return string
   */
  public function getThemeData(string $datum, string $default = ''): string
  {
    if (!isset($this->themeData)) {
      $this->themeData = wp_get_theme();
    }
    
    // sadly, the get method of the WP_Theme object returns false when it
    // can't find the information requested.  additionally sad, the theme likes
    // capitalized names for its information but the average human may not know
    // that.  so, we'll try the exact $datum.  if that's false, we try to
    // capitalize it and see what happens.
    
    $value = $this->themeData->get($datum);
    
    if ($value === false) {
      $value = $this->themeData->get(ucfirst($datum));
    }
    
    return $value === false ? $default : (string) $value;
  }
  
  
  /**
   * register
   *
   * Registers either a script or a style for later use.
   *
   * @param string $file
   * @param array  $dependencies
   * @param null   $finalArg
   * @param string $url
   * @param string $dir
   *
   * @return string
   */
  protected function register(string $file, array $dependencies = [], $finalArg = null, string $url = "", string $dir = ""): string
  {
    // the work of registering an asset is the same as enqueuing one except
    // for the function we call at the end.  thus, we can call our enqueue
    // method but we pass the Boolean true flag as the final parameter that
    // will cause it to execute either wp_register_style or wp_register_script
    // instead of the similarly named enqueue functions.
    
    return $this->enqueue($file, $dependencies, $finalArg, $url, $dir, true);
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
   * @param bool             $register
   *
   * @return string
   */
  protected function enqueue(string $file, array $dependencies = [], $finalArg = null, string $url = "", string $dir = "", bool $register = false): string  {
    // remote assets (e.g. Google fonts) may begin with an HTTP protocol
    // string.  we'll remove that to force browsers to load remote assets using
    // the same protocol as the rest of the page.
    
    $file = preg_replace("/^https?:/", "", $file);
    if (substr($file, 0, 2) === "//") {
      
      // if our $file begins with // then it's remote.  therefore, we'll pass
      // control over to the method below which specifically handles remote
      // assets differently than we handle local assets below.
      
      return $this->enqueueRemote($file, $dependencies, $finalArg);
    }
    
    $asset = pathinfo($file, PATHINFO_FILENAME);
    
    // now that we know what we're working with, we need to determine what
    // we're here to do.  first:  we see if this is a script or a style based
    // on the extension of our file.  then, we determine our action based on
    // the state of the $register parameter and construct the function we call
    // below using that action and our file type.
    
    $isScript = pathinfo($file, PATHINFO_EXTENSION) === 'js';
    $action = $register ? 'register' : 'enqueue';
    $type = $isScript ? 'script' : 'style';
    $function = sprintf('wp_%s_%s', $action, $type);
    
    // if either (or both) of our url or dir parameters is empty, we set it to
    // the stylesheets url or dir as appropriate.  we also make sure that these
    // end in a slash.
    
    $url = trailingslashit(empty($url) ? $this->getStylesheetUrl() : $url);
    $dir = trailingslashit(empty($dir) ? $this->getStylesheetDir() : $dir);
    
    if (is_null($finalArg)) {
      
      // the final argument for our $function is either a Boolean or a string
      // for scripts and styles respectively.  if it's null at the moment,
      // we'll default it to the following.  otherwise, we assume the calling
      // scope knows what it's doing.
      
      $finalArg = $isScript ? true : "all";
    }
    
    // and, now we can enqueue.  we call our $function and pass it a bunch of
    // stuff.  note that we specify the FQDN for the local asset by prefixing
    // the filename with the URL.  we also use the last modified timestamp of
    // the file as our "version" which should force browser to clear their
    // cache of these assets when the file changes.
    
    $function($asset, ($url . $file), $dependencies, filemtime($dir . $file), $finalArg);
    return $asset;
  }
  
  /**
   * enqueueRemote
   *
   * Returns the name of the asset used by WordPress to manage queued
   * dependencies.
   *
   * @param string     $file
   * @param array      $dependencies
   * @param mixed|null $finalArg
   *
   * @return string
   */
  private function enqueueRemote(string $file, array $dependencies, $finalArg = null): string
  {
    // enqueuing a remote asset is a little easier than the local stuff we
    // handled above.  because it can be hard to impossible to accurately
    // identify the filename of a remote asset with pathinfo, we'll just hash
    // $file and use that as our asset's name.  similarly, getting the
    // extension with pathinfo doesn't work well, so we'll just look for
    // the extension ourselves.
    
    $asset = md5($file);
    $isScript = strpos($file, '.js') !== false;
    $function = $isScript ? "wp_enqueue_script" : "wp_enqueue_style";
    if (is_null($finalArg)) {
      
      // the final argument for our $function is either a Boolean or a string
      // for scripts and styles respectively.  if it's null at the moment,
      // we'll default it to the following.  otherwise, we assume the calling
      // scope knows what it's doing.
      
      $finalArg = $isScript ? true : "all";
    }
    
    // and that's it.  we can call our function passing it the values we've
    // identified.  for local assets we use the last modified timestamp of the
    // file as a "version" but here we just use the year and month so that
    // browsers will update their caches periodically but not too often.
    
    $function($asset, $file, $dependencies, date('Ym'), $finalArg);
    return $asset;
  }
  
  /**
   * parentEnqueue
   *
   * Enqueues an asset from within a parent theme's folder.  Throws an
   * exception if this is not a child theme.
   *
   * @param string $file
   * @param array  $dependencies
   * @param null   $finalArg
   * @param bool   $register
   *
   * @return string
   * @throws HandlerException
   */
  protected function enqueueParent(string $file, array $dependencies = [], $finalArg = null, bool $register = false): string
  {
    if (!is_child_theme()) {
      throw new HandlerException($this->getThemeData('name') . ' is not a child theme.',
        HandlerException::NOT_A_CHILD);
    }
    
    // now that we've confirmed this is a child theme, all we need to do is
    // call the enqueue method above and specify the URI and folder for its
    // parent, the template, so that we override the defaults in that method.
    
    return $this->enqueue($file, $dependencies, $finalArg,
      get_template_directory_uri(), get_template_directory(), $register);
  }
}

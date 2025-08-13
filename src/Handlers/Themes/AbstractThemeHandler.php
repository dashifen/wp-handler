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
   * Activates and deactivates required plugins as this theme is activated or
   * deactivated.
   *
   * @param array $plugins
   *
   * @return void
   * @throws HandlerException
   */
  protected function handlePluginDependencies(array $plugins): void
  {
    // before we do anything, let's make sure that the information in $plugins
    // refers to plugins that are actually installed on this site.  we get the
    // list of installed plugins, and then we can use array diff to see if
    // there are any data in $plugins that aren't found in that list.  if so,
    // we throw a HandlerException because this method is for dependencies, and
    // we'll assume that if we're missing something on which we depend that a
    // dev needs to fix that!
    
    $installedPlugins = array_keys(get_plugins());
    $missingPlugins = array_diff($plugins, $installedPlugins);
    if (($count = sizeof($missingPlugins)) > 0) {
      $noun = $count === 1 ? 'Plugin' : 'Plugins';
      $message = "$noun Not Found: " . join(', ', $missingPlugins);
      throw new HandlerException($message, HandlerException::UNKNOWN_PLUGIN);
    }
    
    // the switch_theme action fires when theme A changes to theme B.  then,
    // the after_switch_theme action is triggered when theme B loads for the
    // first time.  so the first action that we create here turns off the
    // required plugins for this theme if we're switching away from it.  the
    // second one turns them on if we're switching to it.
    
    $this->addAction('switch_theme', fn() => deactivate_plugins($plugins));
    $this->addAction('after_switch_theme', fn() => activate_plugins($plugins));
  }
  
  /**
   * Deactivates incompatible plugins when this theme is activated.
   *
   * @param array $plugins
   *
   * @return void
   * @throws HandlerException
   */
  protected function handlePluginIncompatibilities(array $plugins): void
  {
    // in the prior method, if we found missing plugins, we quit because that
    // method focused on dependencies.  this one focuses on incompatibilities
    // which means we don't need to quit, but we can't try to deactivate a
    // plugin that doesn't exist because Core may stop at that one and not
    // deactivate other ones in the list.  so, let's find the intersection
    // between our list and the installed plugins and deactivate only those.
    
    $this->addAction('after_switch_theme', function () use ($plugins) {
      $installedPlugins = array_keys(get_plugins());
      $intersection = array_intersect($installedPlugins, $plugins);
      deactivate_plugins($intersection);
    });
  }
  
  /**
   * register
   *
   * Registers either a script or a style for later use.
   *
   * @param string           $file
   * @param array            $dependencies
   * @param string|bool|null $finalArg
   * @param string           $url
   * @param string           $dir
   *
   * @return string
   */
  protected function register(
    string $file,
    array $dependencies = [],
    string|bool|null $finalArg = null,
    string $url = "",
    string $dir = ""
  ): string {
    // the work of registering an asset is the same as enqueuing one except
    // for the function we call at the end.  thus, we can call our enqueue
    // method, but we pass the Boolean true flag as the final parameter that
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
  protected function enqueue(
    string $file,
    array $dependencies = [],
    string|bool|null $finalArg = null,
    string $url = "",
    string $dir = "",
    bool $register = false
  ): string {
    foreach (['script', 'style'] as $type) {
      [$is, $enqueue] = ['wp_' . $type . '_is', 'wp_enqueue_' . $type];
      
      // the above statement assigns core function names to our $is and
      // $enqueue variables.  these will be wp_script_is and wp_enqueue_script
      // or the similarly named style functions.  then, if the asset we're
      // enqueueing has already been registered, all we need to do is call the
      // enqueue function and return.
      
      if ($is($file, 'registered')) {
        $enqueue($file);
        return $file;
      }
    }
    
    // if we didn't return in the foreach loop above, then this asset has not
    // yet been registered.  that means we do a bit more work here to get
    // things ready.  first, remote assets (e.g. Google fonts) may begin with
    // an HTTP protocol string.  we'll remove that to force browsers to load
    // remote assets using the same protocol as the rest of the page.
    
    $file = preg_replace("/^https?:/", "", $file);
    if (str_starts_with($file, "//")) {
      
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
   * @param string           $file
   * @param array            $dependencies
   * @param string|bool|null $finalArg
   *
   * @return string
   */
  private function enqueueRemote(string $file, array $dependencies, string|bool|null $finalArg = null): string
  {
    // enqueuing a remote asset is a little easier than the local stuff we
    // handled above.  because it can be hard to impossible to accurately
    // identify the filename of a remote asset with pathinfo, we'll just hash
    // $file and use that as our asset's name.  similarly, getting the
    // extension with pathinfo doesn't work well, so we'll just look for
    // the extension ourselves.
    
    $asset = md5($file);
    $isScript = preg_match('/\.[cm]?js$/', $file);
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

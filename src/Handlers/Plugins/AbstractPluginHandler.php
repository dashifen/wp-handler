<?php

namespace Dashifen\WPHandler\Handlers\Plugins;

use Dashifen\Container\ContainerException;
use Dashifen\WPHandler\Containers\MenuItem;
use Dashifen\WPHandler\Containers\MenuItemException;
use ReflectionClass;
use ReflectionException;
use Dashifen\WPHandler\Handlers\AbstractHandler;

abstract class AbstractPluginHandler extends AbstractHandler implements PluginHandlerInterface {
  /**
   * @var string
   */
  protected $pluginDir = "";

  /**
   * @var string
   */
  protected $pluginUrl = "";

  public function __construct () {
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
  final protected function getPluginDirectory (): string {
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
  protected function enqueue (string $file, array $dependencies = [], $finalArg = null, string $url = "", string $dir = ""): string {

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

  /**
   * addMenuPage
   *
   * A wrapper for the WordPress core function of similar name that registers
   * the callback function as a Hook.
   *
   * @param string   $pageTitle
   * @param string   $menuTitle
   * @param string   $capability
   * @param string   $menuSlug
   * @param string   $method
   * @param string   $iconUrl
   * @param int|null $position
   *
   * @return string
   * @throws MenuItemException
   */
  public function addMenuPage (string $pageTitle, string $menuTitle, string $capability, string $menuSlug, string $method = "", string $iconUrl = '', ?int $position = null): string {

    // the primary difference between our wrapper and the core WP function
    // of similar name is that we simply receive the name of a $method to use
    // as our callback instead of a callable of some kind.  here, we want to
    // add that method as a Hook within this Handler object and also call the
    // WP function which actually adds the menu item.

    try {
      $menuItem = new MenuItem(func_get_args());
      $menuItem->setCallable([$this, $method]);
      $loadingHook = add_menu_page(...$menuItem->toArray());
      return $loadingHook;
    } catch (ContainerException $e) {

      // rather than throwing a ContainerException, we'll "convert" it
      // to the type of exception that's more specific to the situation
      // in which we find ourselves.

      throw new MenuItemException($e->getMessage(), $e->getCode(), $e);
    }
  }

  /**
   * addSubmenuPage
   *
   * A wrapper for the WordPress core function of similar name that registers
   * the callback function as a Hook.
   *
   * @param string $parentSlug
   * @param string $pageTitle
   * @param string $menuTitle
   * @param string $capability
   * @param string $menuSlug
   * @param string $method
   *
   * @return string
   * @throws MenuItemException
   */
  public function addSubmenuPage (string $parentSlug, string $pageTitle, string $menuTitle, string $capability, string $menuSlug, string $method = ""): string {

    // like the prior method, here we want to set up our submenu page and
    // add a Hook for its callback to this Handler's list of hooked methods.

    try {
      $menuItem = new MenuItem(func_get_args());
      $menuItem->setCallable([$this, $method]);

      // here we create the submenu page in the Dashboard menu, but we
      // have not yet created the Hook to it.  the name of the hook used
      // by WordPress as the action for a submenu page is created by
      // combining the menu and parent slugs.  we'll call the same WP
      // internal function here as it does.

      $pageDisplayHookname = get_plugin_page_hookname($menuItem->menuSlug, $menuItem->parentSlug);
      $this->addAction($pageDisplayHookname, $menuItem->method);
      return add_submenu_page(...$menuItem->toArray());
    } catch (ContainerException $e) {

      // rather than throwing a general container exception, we'll toss a
      // MenuItemExcpetion instead in order to be as precise as possible.
      // it'll make catching it elsewhere easier.

      throw new MenuItemException($e->getMessage(), $e->getCode(), $e);
    }
  }

  /**
   * addDashboardPage
   *
   * A wrapper for the WordPress core function of similar name that registers
   * the callback function as a Hook.
   *
   * @param string $pageTitle
   * @param string $menuTitle
   * @param string $capability
   * @param string $menuSlug
   * @param string $method
   *
   * @return string
   * @throws MenuItemException
   */
  public function addDashboardPage (string $pageTitle, string $menuTitle, string $capability, string $menuSlug, string $method = ""): string {

    // this, and the subsequent methods, use the spread operator and the
    // func_get_args() function to pass the parameters to this method over
    // to addSubmenuPage() without having to re-type them all.  the function
    // returns an array of the parameters in the declared order.  the spread
    // operator then takes that array and re-creates individual variables
    // to pass them over to the other method.  for more information, see
    // http://ow.ly/uXmU30oXtYo (php.net).

    return $this->addSubmenuPage("index.php", ...func_get_args());
  }

  /**
   * addPostsPage
   *
   * A wrapper for the WordPress core function of similar name that registers
   * the callback function as a Hook.
   *
   * @param string $pageTitle
   * @param string $menuTitle
   * @param string $capability
   * @param string $menuSlug
   * @param string $method
   *
   * @return string
   * @throws MenuItemException
   */
  public function addPostsPage (string $pageTitle, string $menuTitle, string $capability, string $menuSlug, string $method = ""): string {
    return $this->addSubmenuPage("edit.php", ...func_get_args());
  }

  /**
   * addMediaPage
   *
   * A wrapper for the WordPress core function of similar name that registers
   * the callback function as a Hook.
   *
   * @param string $pageTitle
   * @param string $menuTitle
   * @param string $capability
   * @param string $menuSlug
   * @param string $method
   *
   * @return string
   * @throws MenuItemException
   */
  public function addMediaPage (string $pageTitle, string $menuTitle, string $capability, string $menuSlug, string $method = ""): string {
    return $this->addSubmenuPage("upload.php", ...func_get_args());
  }

  /**
   * addCommentsPage
   *
   * A wrapper for the WordPress core function of similar name that registers
   * the callback function as a Hook.
   *
   * @param string $pageTitle
   * @param string $menuTitle
   * @param string $capability
   * @param string $menuSlug
   * @param string $method
   *
   * @return string
   * @throws MenuItemException
   */
  public function addCommentsPage (string $pageTitle, string $menuTitle, string $capability, string $menuSlug, string $method = ""): string {
    return $this->addSubmenuPage("edit-comments.php", ...func_get_args());
  }

  /**
   * addThemePage
   *
   * A wrapper for the WordPress core function of similar name that registers
   * the callback function as a Hook.
   *
   * @param string $pageTitle
   * @param string $menuTitle
   * @param string $capability
   * @param string $menuSlug
   * @param string $method
   *
   * @return string
   * @throws MenuItemException
   */
  public function addThemePage (string $pageTitle, string $menuTitle, string $capability, string $menuSlug, string $method = ""): string {
    return $this->addSubmenuPage("themes.php", ...func_get_args());
  }

  /**
   * addPluginsPage
   *
   * A wrapper for the WordPress core function of similar name that registers
   * the callback function as a Hook.
   *
   * @param string $pageTitle
   * @param string $menuTitle
   * @param string $capability
   * @param string $menuSlug
   * @param string $method
   *
   * @return string
   * @throws MenuItemException
   */
  public function addPluginsPage (string $pageTitle, string $menuTitle, string $capability, string $menuSlug, string $method = ""): string {
    return $this->addSubmenuPage("plugins.php", ...func_get_args());
  }

  /**
   * addUsersPage
   *
   * A wrapper for the WordPress core function of similar name that registers
   * the callback function as a Hook.
   *
   * @param string $pageTitle
   * @param string $menuTitle
   * @param string $capability
   * @param string $menuSlug
   * @param string $method
   *
   * @return string
   * @throws MenuItemException
   */
  public function addUsersPage (string $pageTitle, string $menuTitle, string $capability, string $menuSlug, string $method = ""): string {
    return $this->addSubmenuPage("users.php", ...func_get_args());
  }

  /**
   * addManagementPage
   *
   * A wrapper for the WordPress core function of similar name that registers
   * the callback function as a Hook.
   *
   * @param string $pageTitle
   * @param string $menuTitle
   * @param string $capability
   * @param string $menuSlug
   * @param string $method
   *
   * @return string
   * @throws MenuItemException
   */
  public function addManagementPage (string $pageTitle, string $menuTitle, string $capability, string $menuSlug, string $method = ""): string {
    return $this->addSubmenuPage("tools.php", ...func_get_args());
  }

  /**
   * addOptionsPage
   *
   * A wrapper for the WordPress core function of similar name that registers
   * the callback function as a Hook.
   *
   * @param string $pageTitle
   * @param string $menuTitle
   * @param string $capability
   * @param string $menuSlug
   * @param string $method
   *
   * @return string
   * @throws MenuItemException
   */
  public function addOptionsPage (string $pageTitle, string $menuTitle, string $capability, string $menuSlug, string $method = ""): string {
    return $this->addSubmenuPage("options-general.php", ...func_get_args());
  }

  /**
   * addPostTypePage
   *
   * A convenience function that allows for easier registration of submenu
   * pages within the menu for a custom post type.
   *
   * @param string $postType
   * @param string $pageTitle
   * @param string $menuTitle
   * @param string $capability
   * @param string $menuSlug
   * @param string $method
   *
   * @return string
   * @throws MenuItemException
   */
  public function addPostTypePage (string $postType, string $pageTitle, string $menuTitle, string $capability, string $menuSlug, string $method = ""): string {

    // unlike the other methods that refer to addSubmenuPage, we can't just
    // use the spread operator and the func_get_args() because of the "extra"
    // $postType parameter for this function.  instead, we'll grab our params,
    // shift off the first one, and spread the rest.

    $params = func_get_args();
    array_shift($params);

    return $this->addSubmenuPage("edit.php?post_type=" . $postType, ...$params);
  }
}

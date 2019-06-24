<?php

namespace Dashifen\WPHandler\Handlers\Plugins;

use ReflectionClass;
use ReflectionException;
use Dashifen\Container\ContainerException;
use Dashifen\WPHandler\Containers\MenuItem;
use Dashifen\WPHandler\Hooks\HookException;
use Dashifen\WPHandler\Containers\SubmenuItem;
use Dashifen\WPHandler\Handlers\AbstractHandler;
use Dashifen\WPHandler\Containers\MenuItemException;

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
   * @param MenuItem $menuItem
   *
   * @return string
   * @throws MenuItemException
   * @throws HookException
   */
  final public function addMenuPage (MenuItem $menuItem): string {

    // the primary difference between our wrapper and the core WP function
    // of similar name is that we simply receive the name of a $method to use
    // as our callback instead of a callable of some kind.  here, we want to
    // add that method as a Hook within this Handler object and also call the
    // WP function which actually adds the menu item.

    $this->hookMenuItem($menuItem);
    return add_menu_page(...$menuItem->toArray());
  }

  /**
   * hookMenuItem
   *
   * Given a menu item, hook it into the WordPress ecosystem so that when
   * someone clicks it in the Dashboard, its content is loaded correctly
   * by this Handler.
   *
   * @param MenuItem $menuItem
   *
   * @return string
   * @throws MenuItemException
   * @throws MenuItemException
   * @throws HookException
   */
  final private function hookMenuItem (MenuItem $menuItem): string {
    if (!$menuItem->isComplete()) {
      throw new MenuItemException("Attempt to use incomplete menu item",
        MenuItemException::ITEM_NOT_READY);
    }

    // WordPress uses the get_plugin_page_hookname() function to produce an
    // unique action hook that is executed when a person clicks each menu item.
    // it's created based on the menu and parent slugs for the item.  we'll
    // call that same function here to produce the hook we need, then we can
    // add it and the menu item's method to this Handler's list of Hooks.
    // note that we get both MenuItem objects and their extension, SubmenuItem,
    // here.  since the latter has a parent slug and the former does not, we
    // use the getter for that property rather than reading it like a "normal"
    // one.

    $displayHook = get_plugin_page_hookname($menuItem->menuSlug, $menuItem->getParentSlug());
    return $this->addAction($displayHook, $menuItem->method);
  }

  /**
   * wpAddMenuPage
   *
   * Adds a menu item using arguments that match the WordPress core
   * add_menu_page() function.
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
   * @throws HookException
   * @throws MenuItemException
   */
  final public function wpAddMenuPage (string $pageTitle, string $menuTitle, string $capability, string $menuSlug, string $method, string $iconUrl = "", ?int $position = null) {
    try {
      $menuItem = new MenuItem([
        "pageTitle"  => $pageTitle,
        "menuTitle"  => $menuTitle,
        "capability" => $capability,
        "menuSlug"   => $menuSlug,
        "method"     => $method,
        "iconUrl"    => $iconUrl,
        "position"   => $position
      ]);
    } catch (ContainerException $e) {

      // rather than throw our general ContainerException, we'll "convert"
      // it into a MenuItemException which is a little most specific for our
      // purposes here.

      throw new MenuItemException($e->getMessage(), $e->getCode(), $e);
    }

    return $this->addMenuPage($menuItem);
  }

  /**
   * addSubmenuPage
   *
   * A wrapper for the WordPress core function of similar name that registers
   * the callback function as a Hook.
   *
   * @param SubmenuItem $submenuItem
   *
   * @return string
   * @throws MenuItemException
   * @throws HookException
   */
  final public function addSubmenuPage (SubmenuItem $submenuItem): string {

    // see the prior method for details of this method; they're both extremely
    // similar

    $this->hookMenuItem($submenuItem);
    return add_submenu_page(...$submenuItem->toArray());
  }

  /**
   * wpAddSubmenuPage
   *
   * Adds a submenu page using a series of arguments like add_submenu_page()
   * core function.
   *
   * @param string $parentSlug
   * @param string $pageTitle
   * @param string $menuTitle
   * @param string $capability
   * @param string $menuSlug
   * @param string $method
   *
   * @return string
   * @throws ContainerException
   * @throws HookException
   */
  public function wpAddSubmenuPage (string $parentSlug, string $pageTitle, string $menuTitle, string $capability, string $menuSlug, string $method): string {
    try {
      $submenuItem = new SubmenuItem([
        "parentSlug" => $parentSlug,
        "pageTitle"  => $pageTitle,
        "menuTitle"  => $menuTitle,
        "capability" => $capability,
        "menuSlug"   => $menuSlug,
      ]);
    } catch (ContainerException $e) {

      // rather than throw our general ContainerException, we'll "convert"
      // it into a MenuItemException which is a little most specific for our
      // purposes here.

      throw new MenuItemException($e->getMessage(), $e->getCode(), $e);
    }

    return $this->addSubmenuPage($submenuItem);
  }

  /**
   * addDashboardPage
   *
   * A wrapper for the WordPress core function of similar name that registers
   * the callback function as a Hook.
   *
   * @param SubmenuItem $submenuItem
   *
   * @return string
   * @throws MenuItemException
   * @throws HookException
   */
  final public function addDashboardPage (SubmenuItem $submenuItem): string {

    // the purpose of this and the following methods is to provide similar
    // methods to the WordPress core functions of similar name mostly for
    // completeness.  they all add the required parent slug to our $submenuItem
    // parameter and then pass it over to the addSubmenuPage() method.

    $submenuItem->setParentSlug("index.php");
    return $this->addSubmenuPage($submenuItem);
  }

  /**
   * addPostsPage
   *
   * A wrapper for the WordPress core function of similar name that registers
   * the callback function as a Hook.
   *
   * @param SubmenuItem $submenuItem
   *
   * @return string
   * @throws MenuItemException
   * @throws HookException
   */
  final public function addPostsPage (SubmenuItem $submenuItem): string {
    $submenuItem->setParentSlug("edit.php");
    return $this->addSubmenuPage($submenuItem);
  }

  /**
   * addMediaPage
   *
   * A wrapper for the WordPress core function of similar name that registers
   * the callback function as a Hook.
   *
   * @param SubmenuItem $submenuItem
   *
   * @return string
   * @throws MenuItemException
   * @throws HookException
   */
  final public function addMediaPage (SubmenuItem $submenuItem): string {
    $submenuItem->setParentSlug("upload.php");
    return $this->addSubmenuPage($submenuItem);
  }

  /**
   * addCommentsPage
   *
   * A wrapper for the WordPress core function of similar name that registers
   * the callback function as a Hook.
   *
   * @param SubmenuItem $submenuItem
   *
   * @return string
   * @throws MenuItemException
   * @throws HookException
   */
  final public function addCommentsPage (SubmenuItem $submenuItem): string {
    $submenuItem->setParentSlug("edit-comments.php");
    return $this->addSubmenuPage($submenuItem);
  }

  /**
   * addThemePage
   *
   * A wrapper for the WordPress core function of similar name that registers
   * the callback function as a Hook.
   *
   * @param SubmenuItem $submenuItem
   *
   * @return string
   * @throws MenuItemException
   * @throws HookException
   */
  final public function addThemePage (SubmenuItem $submenuItem): string {
    $submenuItem->setParentSlug("themes.php");
    return $this->addSubmenuPage($submenuItem);
  }

  /**
   * addAppearancePage
   *
   * A wrapper for addThemePage because this is the name of the parent
   * menu item in the WP Dashboard so people might want to try and use it
   * as a method here.
   *
   * @param SubmenuItem $submenuItem
   *
   * @return string
   * @throws MenuItemException
   * @throws HookException
   */
  final public function addAppearancePage (SubmenuItem $submenuItem): string {
    return $this->addThemePage($submenuItem);
  }

  /**
   * addPluginsPage
   *
   * A wrapper for the WordPress core function of similar name that registers
   * the callback function as a Hook.
   *
   * @param SubmenuItem $submenuItem
   *
   * @return string
   * @throws MenuItemException
   * @throws HookException
   */
  final public function addPluginsPage (SubmenuItem $submenuItem): string {
    $submenuItem->setParentSlug("plugins.php");
    return $this->addSubmenuPage($submenuItem);
  }

  /**
   * addUsersPage
   *
   * A wrapper for the WordPress core function of similar name that registers
   * the callback function as a Hook.
   *
   * @param SubmenuItem $submenuItem
   *
   * @return string
   * @throws MenuItemException
   * @throws HookException
   */
  final public function addUsersPage (SubmenuItem $submenuItem): string {
    $submenuItem->setParentSlug("users.php");
    return $this->addSubmenuPage($submenuItem);
  }

  /**
   * addManagementPage
   *
   * A wrapper for the WordPress core function of similar name that registers
   * the callback function as a Hook.
   *
   * @param SubmenuItem $submenuItem
   *
   * @return string
   * @throws MenuItemException
   * @throws HookException
   */
  final public function addManagementPage (SubmenuItem $submenuItem): string {
    $submenuItem->setParentSlug("tools.php");
    return $this->addSubmenuPage($submenuItem);
  }

  /**
   * addToolsPage
   *
   * A wrapper for the addManagementPage that uses the name of the Dashboard
   * menu item to which this one will be added.
   *
   * @param SubmenuItem $submenuItem
   *
   * @return string
   * @throws MenuItemException
   * @throws HookException
   */
  final public function addToolsPage (SubmenuItem $submenuItem): string {
    return $this->addManagementPage($submenuItem);
  }

  /**
   * addOptionsPage
   *
   * A wrapper for the WordPress core function of similar name that registers
   * the callback function as a Hook.
   *
   * @param SubmenuItem $submenuItem
   *
   * @return string
   * @throws MenuItemException
   * @throws HookException
   */
  final public function addOptionsPage (SubmenuItem $submenuItem): string {
    $submenuItem->setParentSlug("options-general.php");
    return $this->addSubmenuPage($submenuItem);
  }

  /**
   * addSettingsPage
   *
   * A wrapper for the addManagementPage that uses the name of the Dashboard
   * menu item to which this one will be added.
   *
   * @param SubmenuItem $submenuItem
   *
   * @return string
   * @throws MenuItemException
   * @throws HookException
   */
  final public function addSettingsPage (SubmenuItem $submenuItem): string {
    return $this->addOptionsPage($submenuItem);
  }

  /**
   * addPostTypePage
   *
   * A convenience function that allows for easier registration of submenu
   * pages within the menu for a custom post type.
   *
   * @param string      $postType
   * @param SubmenuItem $submenuItem
   *
   * @return string
   * @throws MenuItemException
   * @throws HookException
   */
  final public function addPostTypePage (string $postType, SubmenuItem $submenuItem): string {
    $submenuItem->setParentSlug("edit.php?post_type=" . $postType);
    return $this->addSubmenuPage($submenuItem);
  }

  /**
   * addPagesPage
   *
   * A wrapper for the addPostTypePage which specifically adds this submenu
   * item to the Pages submenu.
   *
   * @param SubmenuItem $submenuItem
   *
   * @return string
   * @throws MenuItemException
   * @throws HookException
   */
  final public function addPagesPage (SubmenuItem $submenuItem): string {
    return $this->addPostTypePage("page", $submenuItem);
  }
}

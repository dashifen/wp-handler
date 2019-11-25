<?php

namespace Dashifen\WPHandler\Handlers\Plugins;

use ReflectionClass;
use ReflectionException;
use Dashifen\Repository\RepositoryException;
use Dashifen\WPHandler\Repositories\MenuItem;
use Dashifen\WPHandler\Repositories\SubmenuItem;
use Dashifen\WPHandler\Handlers\HandlerException;
use Dashifen\WPHandler\Repositories\MenuItemException;
use Dashifen\WPHandler\Hooks\Factory\HookFactoryInterface;
use Dashifen\WPHandler\Handlers\Themes\AbstractThemeHandler;
use Dashifen\WPHandler\Hooks\Collection\Factory\HookCollectionFactoryInterface;

/**
 * Class AbstractPluginHandler
 *
 * Note that this object extends the AbstractThemeHandler and not the more
 * general AbstractHandler.  This is because plugins may still need to know
 * about the theme's directory and URL and because we can extend the theme's
 * enqueue() method to work with our plugin assets with this extension as
 * well.
 *
 * @package Dashifen\WPHandler\Handlers\Plugins
 */
abstract class AbstractPluginHandler extends AbstractThemeHandler implements PluginHandlerInterface {
  /**
   * @var string
   */
  protected $pluginDir = "";

  /**
   * @var string
   */
  protected $pluginUrl = "";

  /**
   * @var string
   */
  protected $pluginFilename = "";

  /**
   * AbstractPluginHandler constructor.
   *
   * @param HookFactoryInterface           $hookFactory
   * @param HookCollectionFactoryInterface $hookCollectionFactory
   */
  public function __construct (
    HookFactoryInterface $hookFactory,
    HookCollectionFactoryInterface $hookCollectionFactory
  ) {
    parent::__construct($hookFactory, $hookCollectionFactory);

    $pluginUrl = WP_PLUGIN_URL . "/" . $this->findPluginDirectory();
    $this->pluginUrl = preg_replace("/^https?:/", "", $pluginUrl);
    $this->pluginDir = WP_PLUGIN_DIR . "/" . $this->findPluginDirectory();
    $this->setPluginFilename(debug_backtrace());
  }

  /**
   * findPluginDirectory
   *
   * Returns the name of the directory in which our concrete extension
   * of this class resides.  Note:  this is different from the other method
   * with a similar name, getPluginDir().  this one is protected and for
   * use internal to this object; that one is to extract the protected value
   * of the pluginDir property.
   *
   * @return string
   */
  protected function findPluginDirectory (): string {
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
   * setPluginFilename
   *
   * Given the backtrace of this PluginHandler's __constructor() call, this
   * method returns
   *
   * @param array $backtrace
   *
   * @return void
   */
  protected function setPluginFilename (array $backtrace = []): void {

    // given the debug_backtrace() results called from our __constructor, we'll
    // identify the plugin filename for WordPress based on that data.  we allow
    // an empty array so that this method can be more easily overridden by
    // extensions of this object.

    if (!empty($backtrace)) {

      // we want to set our property to <dir>/<file> for the file in which this
      // plugin's WP block comment is located.  it should be listed in the file index
      // of the first value in the $backtrace array.

      $file = array_shift($backtrace)['file'] ?? '';
      if (!empty($file)) {

        // if we've identified our file, we want to break its path into its
        // component parts.  then, we slice off everything but the final two
        // and re-join those with a forward slash to produce the <dir>/<file>
        // that we need.

        $fileParts = explode(DIRECTORY_SEPARATOR, $file);
        $dirAndFile = array_slice($fileParts, -2);
        $this->pluginFilename = join('/', $dirAndFile);
      }
    }
  }

  /**
   * getPluginDir
   *
   * Returns the path to the directory containing this plugin.
   *
   * @return string
   */
  public function getPluginDir (): string {
    return $this->pluginDir;
  }

  /**
   * getPluginUrl
   *
   * Returns the path to the URL for the directory containing this plugin.
   *
   * @return string
   */
  public function getPluginUrl (): string {
    return $this->pluginUrl;
  }

  /**
   * getPluginFilename
   *
   * Returns the WP-style plugin filename which is <dir>/<file> for the file
   * in which the WP plugin header is located.
   *
   * @return string
   */
  public function getPluginFilename (): string {
    return $this->pluginFilename;
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
   * registerActivationHook
   *
   * Hooks the method provided to the WordPress ecosystem so that the
   * method is executed when this plugin is activated.
   *
   * @param string $method
   *
   * @return string
   * @throws HandlerException
   */
  public function registerActivationHook (string $method): string {

    // WordPress's register_activation_hook() function simply attaches a
    // "normal" callable to the activate_<plugin-filename> hook.  so, all
    // we need to do here is perform that hook in the way that works for
    // our Handler objects.

    return $this->addAction('activate_' . $this->getPluginFilename(), $method);
  }

  /**
   * registerDeactivationHook
   *
   * Hooks the method provided to the WordPress ecosystem so that the
   * method is executed when this plugin is deactivated.
   *
   * @param string $method
   *
   * @return string
   * @throws HandlerException
   */
  public function registerDeactivationHook (string $method): string {
    return $this->addAction('deactivate_' . $this->getPluginFilename(), $method);
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
   * @throws HandlerException
   */
  final public function addMenuPage (MenuItem $menuItem): string {

    // given a MenuItem we want to add it to WordPress.  we have to call
    // the WP Core add_menu_page() method first because whether or not the
    // page is registered within WordPress changes the name of the action
    // hook used when executing the menu item's callback.

    $success = add_menu_page(...$menuItem->toArray());
    $this->hookMenuItem($menuItem);
    return $success;
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
   * @throws HandlerException
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
   * @throws HandlerException
   * @throws MenuItemException
   */
  final public function wpAddMenuPage (string $pageTitle, string $menuTitle, string $capability, string $menuSlug, string $method, string $iconUrl = "", ?int $position = null) {
    try {
      $menuItem = new MenuItem($this, [
        "pageTitle"  => $pageTitle,
        "menuTitle"  => $menuTitle,
        "capability" => $capability,
        "menuSlug"   => $menuSlug,
        "method"     => $method,
        "iconUrl"    => $iconUrl,
        "position"   => $position
      ]);
    } catch (RepositoryException $e) {

      // rather than throw our general RepositoryException, we'll "convert"
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
   * @throws HandlerException
   */
  final public function addSubmenuPage (SubmenuItem $submenuItem): string {

    // like addMenuPage, but this one gets a SubmenuItem.  as before, the
    // action hook used to execute the submenu item's callback will be
    // different once WP Core knows about it, so we'll call its function
    // first and then hook it up here.

    $success = add_submenu_page(...$submenuItem->toArray());
    $this->hookMenuItem($submenuItem);
    return $success;
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
   * @throws RepositoryException
   * @throws HandlerException
   */
  public function wpAddSubmenuPage (string $parentSlug, string $pageTitle, string $menuTitle, string $capability, string $menuSlug, string $method): string {
    try {
      $submenuItem = new SubmenuItem($this, [
        "parentSlug" => $parentSlug,
        "pageTitle"  => $pageTitle,
        "menuTitle"  => $menuTitle,
        "capability" => $capability,
        "menuSlug"   => $menuSlug,
      ]);
    } catch (RepositoryException $e) {

      // rather than throw our general RepositoryException, we'll "convert"
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
   * @throws HandlerException
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
   * @throws HandlerException
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
   * @throws HandlerException
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
   * @throws HandlerException
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
   * @throws HandlerException
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
   * @throws HandlerException
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
   * @throws HandlerException
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
   * @throws HandlerException
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
   * @throws HandlerException
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
   * @throws HandlerException
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
   * @throws HandlerException
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
   * @throws HandlerException
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
   * @throws HandlerException
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
   * @throws HandlerException
   */
  final public function addPagesPage (SubmenuItem $submenuItem): string {
    return $this->addPostTypePage("page", $submenuItem);
  }
}

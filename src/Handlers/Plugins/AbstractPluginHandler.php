<?php

namespace Dashifen\WPHandler\Handlers\Plugins;

use Dashifen\Repository\RepositoryException;
use Dashifen\WPHandler\Handlers\HandlerException;
use Dashifen\WPHandler\Repositories\MenuItems\MenuItem;
use Dashifen\WPHandler\Repositories\MenuItems\SubmenuItem;
use Dashifen\WPHandler\Hooks\Factory\HookFactoryInterface;
use Dashifen\WPHandler\Handlers\Themes\AbstractThemeHandler;
use Dashifen\WPHandler\Repositories\MenuItems\MenuItemException;
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
   * @var string
   */
  private $pluginId = "";

  /**
   * AbstractPluginHandler constructor.
   *
   * @param HookFactoryInterface|null           $hookFactory
   * @param HookCollectionFactoryInterface|null $hookCollectionFactory
   *
   * @throws HandlerException
   */
  public function __construct (
    ?HookFactoryInterface $hookFactory = null,
    ?HookCollectionFactoryInterface $hookCollectionFactory = null
  ) {
    parent::__construct($hookFactory, $hookCollectionFactory);

    $directory = $this->getCurrentDirectory();
    $this->pluginDir = WP_PLUGIN_DIR . '/' . $directory;
    $this->pluginUrl = WP_PLUGIN_URL . '/' . $directory;

    // we'll remove the HTTP protocol from our URL so that assets enqueued by
    // this object are included into the DOM using the same protocol as the
    // original HTTP request.  this avoids any mixed HTTP vs. HTTPS warnings
    // that a browser might have otherwise thrown.

    $this->pluginUrl = preg_replace('/^https?:/', '', $this->pluginUrl);

    // our plugin ID is the SHA1 hash of the static class name for this object.
    // while it's possible that this might collide with another one, it's so
    // unlikely that we're not going to worry too much about it.  plus, better
    // hashes produce longer results, and WP recommends option names that are
    // no more than 64 characters long.

    $this->pluginId = sha1(static::class);
    $this->setPluginFilename();

    // typically, a plugin should not remove options when deactivating, but
    // there's no easy way to do so when the plugin is uninstalled from here.
    // so, because we will have cached a few options related to this plugin
    // we'll want to clean them up when we deactivate.  they'll be recreated
    // if it's reactivated so it's not such a big deal this time.

    $this->registerDeactivationHook('uncachePluginFilename');
  }

  /**
   * getCurrentDirectory
   *
   * Returns the name of the directory in which our concrete extension
   * of this class resides.  Note:  this is different from the other method
   * with a similar name, getPluginDir().  this one is protected and for
   * use internal to this object; that one is to extract the protected value
   * of the pluginDir property.
   *
   * @return string
   */
  private function getCurrentDirectory (): string {

    // to get the directory in which our object's concrete extension is
    // defined, we use a reflection to get the filename.  then, we can use that
    // to get its directory, explode that path, and return just the final
    // directory in which our class resides.

    $directory = dirname($this->handlerReflection->getFilename());
    $directoryParts = explode(DIRECTORY_SEPARATOR, $directory);
    return array_pop($directoryParts);
  }

  /**
   * setPluginFilename
   *
   * Given a backtrace of the call stack that lead us here, identify the plugin
   * definition file within that stack.  Note:  it may reside in a different
   * folder from the one we identify using getCurrentDirectory.
   *
   * @param array|null $backtrace
   *
   * @return void
   * @throws HandlerException
   */
  private function setPluginFilename (?array $backtrace = null): void {
    if (is_null($backtrace)) {

      // if a call stack backtrace wasn't provided, we'll create one here.
      // we can skip function/method arguments because we're not using those
      // data and doing so saves some memory.

      $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
    }

    // because looping over the backtrace and opening files to check for the WP
    // plugin header is an expensive prospect, we may have cached the filename
    // that we previously identified in the database.  we'll try to rely on
    // that and only do the more expensive search when we absolutely have to.

    $this->pluginFilename = $this->maybeGetPluginFilename();

    if (is_null($this->pluginFilename)) {
      $this->pluginFilename = $this->findPluginFilename($backtrace);
      $this->cachePluginFilename();
    }
  }

  /**
   * maybeGetPluginFilename
   *
   * Returns a previously cached plugin filename or null if it's too old to use
   * or doesn't exist.
   *
   * @return string|null
   */
  private function maybeGetPluginFilename (): ?string {

    // if the last time that we stored our plugin filename is before the last
    // time that our plugin class file was modified, then we're going to force
    // this system to re-identify the plugin filename in case the larger
    // structure of the plugin changed, too.  otherwise, we just return our
    // cache or null if the cache is empty.

    $lastFileMod = filemtime($this->handlerReflection->getFileName());
    $timestamp = get_option($this->pluginId . '-pluginFilenameTimestamp', 0);

    return $timestamp > $lastFileMod
      ? get_option($this->pluginId . '-pluginFilename', null)
      : null;
  }

  /**
   * findPluginFilename
   *
   * Searches through a call stack backtrace looking for the file that defines
   * our plugin using the WP plugin header.
   *
   * @param array $backtrace
   *
   * @return string
   * @throws HandlerException
   */
  private function findPluginFilename (array $backtrace): string {

    // we assume that backtrace is the output of the debug_backtrace()
    // function.  if it's anything else, this probably won't work.

    foreach ($backtrace as $trace) {
      $file = $trace['file'] ?? false;
      if ($file && $this->isPluginFile($file)) {

        // if we're all the way in here, we've found our plugin definition
        // file.  WordPress wants our plugin's filename to the directory in
        // which it can be found followed by the actual filename.  so, to
        // build a string that is <dir>/<filename> we break up $file by the
        // directory separator, slice off the last two items, and then join
        // them with an forward slash.  after we set our property, we're done.

        $pathParts = explode(DIRECTORY_SEPARATOR, $file);
        $filenameParts = array_slice($pathParts, -2);
        return join('/', $filenameParts);
      }
    }

    // if we make it here, then we somehow traveled all the way through our
    // backtrace and did not find our plugin filename.  this shouldn't happen,
    // but if it does, we'll throw an Exception and hope the programmer can
    // fix it.

    throw new HandlerException('Unable to identify plugin file');
  }

  /**
   * isPluginFile
   *
   * Checks within $file to see if it has the WordPress plugin header comment.
   *
   * @param string $file
   *
   * @return bool
   */
  private function isPluginFile (string $file): bool {

    // here we read the first 8KB of our file.  why 8KB?  because that's what
    // the core get_file_data() function does.  why don't we use that one?
    // because it is not included (yet).

    $fp = fopen($file, 'r');
    $data = fread($fp, 1024 * 8);
    fclose($fp);

    // now, to determine if this is the plugin definition file, we look for the
    // "Plugin Name:" string what we've read.  this is required by WordPress in
    // order for our plugin to be a plugin; the other plugin header "tags"
    // aren't.

    return strpos($data, 'Plugin Name:') !== false;
  }

  /**
   * cachePluginFilename
   *
   * Stores the identified plugin filename in the database along with the
   * current timestamp so we can tell if it might be out of date later.
   *
   * @return void
   */
  private function cachePluginFilename (): void {

    // we set the autoload flag for these options to true so that they're
    // selected along with the other autoloaded options.  since they'll be
    // needed every time the plugin is needed, we save database query time by
    // doing so.

    update_option($this->pluginId . '-pluginFilename', $this->pluginFilename, true);
    update_option($this->pluginId . '-pluginFilenameTimestamp', time(), true);
  }

  /**
   * uncachePluginFilename
   *
   * Hooked to the deactivation action, this method simply removes the data
   * that we cached above.  this one is protected because it becomes a callback
   * during this handler's deactivation hook.
   *
   * @return void
   */
  protected function uncachePluginFilename (): void {
    delete_option($this->pluginId . '-pluginFilenameTimestamp');
    delete_option($this->pluginId . '-pluginFilename');
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
      throw new MenuItemException('Attempt to use incomplete menu item',
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
   * @throws MenuItemException
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

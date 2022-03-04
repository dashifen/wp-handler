<?php

namespace Dashifen\WPHandler\Handlers\Plugins;

use Dashifen\WPHandler\Repositories\MenuItems\MenuItem;
use Dashifen\WPHandler\Repositories\MenuItems\SubmenuItem;
use Dashifen\WPHandler\Handlers\Themes\ThemeHandlerInterface;

/**
 * Interface PluginHandlerInterface
 *
 * Notice that this interface extends the ThemeHandlerInterface, not the
 * more general HandlerInterface.  That's because plugins might still need
 * to know about the location of the Theme's stylesheet and we extend the
 * AbstractThemeHandler's enqueue() method as well.
 *
 * @package Dashifen\WPHandler\Handlers\Plugins
 */
interface PluginHandlerInterface extends ThemeHandlerInterface
{
  /**
   * getPluginFilename
   *
   * Returns the WP-style plugin filename which is <dir>/<file> for the file
   * in which the WP plugin header is located.
   *
   * @param bool $withoutDir
   *
   * @return string
   */
  public function getPluginFilename(bool $withoutDir = false): string;
  
  /**
   * getPluginFilenameWithoutDirectory
   *
   * A convenience method that calls getPluginFilename and passes it a true
   * flag.  This helps make code utilizing this object a little more self-
   * explanatory.
   *
   * @return string
   */
  public function getPluginFilenameWithoutDirectory(): string;
  
  /**
   * getPluginDir
   *
   * Returns the path to the directory containing this plugin.
   *
   * @return string
   */
  public function getPluginDir(): string;
  
  /**
   * getPluginUrl
   *
   * Returns the path to the URL for the directory containing this plugin.
   *
   * @return string
   */
  public function getPluginUrl(): string;
  
  /**
   * getPluginData
   *
   * Returns information about this plugin internally using the WP Core
   * get_plugin_data function.
   *
   * @param string $datum
   * @param string $default
   *
   * @return string
   */
  public function getPluginData(string $datum, string $default = ''): string;
  
  /**
   * registerActivationHook
   *
   * Hooks the method provided to the WordPress ecosystem so that the
   * method is executed when this plugin is activated.
   *
   * @param string $method
   *
   * @return string
   */
  public function registerActivationHook(string $method): string;
  
  /**
   * registerDeactivationHook
   *
   * Hooks the method provided to the WordPress ecosystem so that the
   * method is executed when this plugin is deactivated.
   *
   * @param string $method
   *
   * @return string
   */
  public function registerDeactivationHook(string $method): string;
  
  /**
   * addMenuPage
   *
   * A wrapper for the WordPress core function of similar name that registers
   * the callback function as a Hook.
   *
   * @param MenuItem $menuItem
   *
   * @return string
   */
  public function addMenuPage(MenuItem $menuItem): string;
  
  /**
   * wpAddMenuPage
   *
   * This function calls the prior one by constructing a MenuItem object
   * first and then calling the other one.  The purpose of this is to provide
   * a method with the same arguments as the core add_menu_page() function for
   * those that don't want to manage their own MenuItem objects.
   *
   * @param string   $pageTitle
   * @param string   $menuTitle
   * @param string   $capability
   * @param string   $menuSlug
   * @param string   $method
   * @param string   $iconUrl
   * @param int|null $position
   *
   * @return mixed
   */
  public function wpAddMenuPage(string $pageTitle, string $menuTitle, string $capability, string $menuSlug, string $method, string $iconUrl = "", ?int $position = null);
  
  /**
   * addSubmenuPage
   *
   * A wrapper for the WordPress core function of similar name that registers
   * the callback function as a Hook.
   *
   * @param SubmenuItem $submenuItem
   *
   * @return string
   */
  public function addSubmenuPage(SubmenuItem $submenuItem): string;
  
  /**
   * wpAddSubmenuPage
   *
   * Like the wpAddMenuPage, this method provides the means to add a submenu
   * item with arguments that match the WordPress world for those who don't
   * want to manage their own SubmenuItem objects.
   *
   * @param string $parentSlug
   * @param string $pageTitle
   * @param string $menuTitle
   * @param string $capability
   * @param string $menuSlug
   * @param string $method
   *
   * @return string
   */
  public function wpAddSubmenuPage(string $parentSlug, string $pageTitle, string $menuTitle, string $capability, string $menuSlug, string $method): string;
  
  /**
   * addDashboardPage
   *
   * A wrapper for the WordPress core function of similar name that registers
   * the callback function as a Hook.
   *
   * @param SubmenuItem $submenuItem
   *
   * @return string
   */
  public function addDashboardPage(SubmenuItem $submenuItem): string;
  
  /**
   * addPostsPage
   *
   * A wrapper for the WordPress core function of similar name that registers
   * the callback function as a Hook.
   *
   * @param SubmenuItem $submenuItem
   *
   * @return string
   */
  public function addPostsPage(SubmenuItem $submenuItem): string;
  
  /**
   * addMediaPage
   *
   * A wrapper for the WordPress core function of similar name that registers
   * the callback function as a Hook.
   *
   * @param SubmenuItem $submenuItem
   *
   * @return string
   */
  public function addMediaPage(SubmenuItem $submenuItem): string;
  
  /**
   * addCommentsPage
   *
   * A wrapper for the WordPress core function of similar name that registers
   * the callback function as a Hook.
   *
   * @param SubmenuItem $submenuItem
   *
   * @return string
   */
  public function addCommentsPage(SubmenuItem $submenuItem): string;
  
  /**
   * addThemePage
   *
   * A wrapper for the WordPress core function of similar name that registers
   * the callback function as a Hook.
   *
   * @param SubmenuItem $submenuItem
   *
   * @return string
   */
  public function addThemePage(SubmenuItem $submenuItem): string;
  
  /**
   * addAppearancePage
   *
   * A wrapper for the addThemePage function because the name of the WP
   * Dashboard menu item is "appearance" and not "theme."
   *
   * @param SubmenuItem $submenuItem
   *
   * @return string
   */
  public function addAppearancePage(SubmenuItem $submenuItem): string;
  
  /**
   * addPluginsPage
   *
   * A wrapper for the WordPress core function of similar name that registers
   * the callback function as a Hook.
   *
   * @param SubmenuItem $submenuItem
   *
   * @return string
   */
  public function addPluginsPage(SubmenuItem $submenuItem): string;
  
  /**
   * addUsersPage
   *
   * A wrapper for the WordPress core function of similar name that registers
   * the callback function as a Hook.
   *
   * @param SubmenuItem $submenuItem
   *
   * @return string
   */
  public function addUsersPage(SubmenuItem $submenuItem): string;
  
  /**
   * addManagementPage
   *
   * A wrapper for the WordPress core function of similar name that registers
   * the callback function as a Hook.
   *
   * @param SubmenuItem $submenuItem
   *
   * @return string
   */
  public function addManagementPage(SubmenuItem $submenuItem): string;
  
  /**
   * addToolsPage
   *
   * A wrapper for the addManagementPage method because this one includes the
   * name of the menu item in the Dashboard to which this submenu item would be
   * added.
   *
   * @param SubmenuItem $submenuItem
   *
   * @return string
   */
  public function addToolsPage(SubmenuItem $submenuItem): string;
  
  /**
   * addOptionsPage
   *
   * A wrapper for the WordPress core function of similar name that registers
   * the callback function as a Hook.
   *
   * @param SubmenuItem $submenuItem
   *
   * @return string
   */
  public function addOptionsPage(SubmenuItem $submenuItem): string;
  
  /**
   * addSettingsPage
   *
   * A wrapper for the addOptionsPage method because this one includes the
   * name of the menu item in the Dashboard to which this submenu item would be
   * added.
   *
   * @param SubmenuItem $submenuItem
   *
   * @return string
   */
  public function addSettingsPage(SubmenuItem $submenuItem): string;
  
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
   */
  public function addPostTypePage(string $postType, SubmenuItem $submenuItem): string;
  
  /**
   * addPagesPage
   *
   * A wrapper for the addPostTypePage that specifically adds a submenu item
   * to the Pages menu since that's a standard CPT within WordPress.
   *
   * @param SubmenuItem $submenuItem
   *
   * @return string
   */
  public function addPagesPage(SubmenuItem $submenuItem): string;
}

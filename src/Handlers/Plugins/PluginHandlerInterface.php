<?php

namespace Dashifen\WPHandler\Handlers\Plugins;

interface PluginHandlerInterface {
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
   */
  public function addMenuPage (string $pageTitle, string $menuTitle, string $capability, string $menuSlug, string $method = "", string $iconUrl = '', ?int $position = null): string;

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
   */
  public function addSubmenuPage (string $parentSlug, string $pageTitle, string $menuTitle, string $capability, string $menuSlug, string $method = ""): string;

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
   */
  public function addDashboardPage (string $pageTitle, string $menuTitle, string $capability, string $menuSlug, string $method = ""): string;

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
   */
  public function addPostsPage (string $pageTitle, string $menuTitle, string $capability, string $menuSlug, string $method = ""): string;

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
   */
  public function addMediaPage (string $pageTitle, string $menuTitle, string $capability, string $menuSlug, string $method = ""): string;

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
   */
  public function addCommentsPage (string $pageTitle, string $menuTitle, string $capability, string $menuSlug, string $method = ""): string;

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
   */
  public function addThemePage (string $pageTitle, string $menuTitle, string $capability, string $menuSlug, string $method = ""): string;

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
   */
  public function addPluginsPage (string $pageTitle, string $menuTitle, string $capability, string $menuSlug, string $method = ""): string;

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
   */
  public function addUsersPage (string $pageTitle, string $menuTitle, string $capability, string $menuSlug, string $method = ""): string;

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
   */
  public function addManagementPage (string $pageTitle, string $menuTitle, string $capability, string $menuSlug, string $method = ""): string;

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
   */
  public function addOptionsPage (string $pageTitle, string $menuTitle, string $capability, string $menuSlug, string $method = ""): string;

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
   */
  public function addPostTypePage (string $postType, string $pageTitle, string $menuTitle, string $capability, string $menuSlug, string $method = ""): string;
}
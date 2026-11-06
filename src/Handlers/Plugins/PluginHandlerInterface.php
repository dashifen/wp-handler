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
}

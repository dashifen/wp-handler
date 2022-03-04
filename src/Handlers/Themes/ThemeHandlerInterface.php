<?php

namespace Dashifen\WPHandler\Handlers\Themes;

use Dashifen\WPHandler\Handlers\HandlerInterface;

/**
 * Interface HandlerInterface
 *
 * @package Dashifen\WPHandler\Handlers\Themes
 */
interface ThemeHandlerInterface extends HandlerInterface
{
  /**
   * getUrl
   *
   * Returns the URL that corresponds to the folder in which this Handler
   * is located.
   *
   * @return string
   */
  public function getStylesheetUrl(): string;
  
  /**
   * getDir
   *
   * Returns the filesystem path to the folder in which this Handler
   * is located.
   *
   * @return string
   */
  public function getStylesheetDir(): string;
  
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
  public function getThemeData(string $datum, string $default = ''): string;
}

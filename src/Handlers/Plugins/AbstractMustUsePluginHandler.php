<?php

namespace Dashifen\WPHandler\Handlers\Plugins;

abstract class AbstractMustUsePluginHandler extends AbstractPluginHandler
{
  /**
   * getWPPluginDir
   *
   * Returns the path to the WP mu-plugin directory using the WPMU_PLUGIN_DIR
   * constant.
   *
   * @return string
   */
  protected function getWPPluginDir(): string
  {
    return WPMU_PLUGIN_DIR;
  }
  
  /**
   * getWPPluginUrl
   *
   * Returns the URL for the WP mu-plugin directory using the WPMU_PLUGIN_URL
   * constant.
   *
   * @return string
   */
  protected function getWPPluginUrl(): string
  {
    return WPMU_PLUGIN_URL;
  }
}

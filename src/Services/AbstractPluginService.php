<?php

namespace Dashifen\WPHandler\Services;

use Dashifen\WPHandler\Handlers\Plugins\AbstractPluginThemeHandler;
use Dashifen\WPHandler\Handlers\Plugins\PluginHandlerInterface;

/**
 * Class AbstractPluginService
 *
 * @package Dashifen\WPHandler\Handlers\Services
 */
abstract class AbstractPluginService extends AbstractPluginThemeHandler {
  /**
   * @var AbstractPluginThemeHandler
   */
  protected $handler;

  /**
   * AbstractPluginService constructor.
   *
   * @param PluginHandlerInterface $handler
   */
  public function __construct (PluginHandlerInterface $handler) {
    $this->handler = $handler;
    parent::__construct();
  }
}
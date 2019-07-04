<?php

namespace Dashifen\WPHandler\Services;

use Dashifen\WPHandler\Handlers\Themes\AbstractThemeHandler;
use Dashifen\WPHandler\Handlers\Themes\ThemeHandlerInterface;

/**
 * Class AbstractPluginService
 *
 * The purpose of a Service is to provide a narrowly focused set of behaviors
 * that can be severed from the main Theme code to help identify and separate
 * responsibilities.  They are, for all intents and purposes, simply other
 * Handlers, but these must be constructed with a link to the other Handler
 * that they serve.
 *
 * @package Dashifen\WPHandler\Handlers\Services
 */
abstract class AbstractThemeService extends AbstractThemeHandler {
  /**
   * @var ThemeHandlerInterface
   */
  protected $handler;

  /**
   * AbstractPluginService constructor.
   *
   * @param ThemeHandlerInterface $handler
   */
  public function __construct (ThemeHandlerInterface $handler) {
    $this->handler = $handler;
    parent::__construct();
  }
}
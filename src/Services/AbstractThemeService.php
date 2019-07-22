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
    parent::__construct($handler->getHookFactory());
    $this->handler = $handler;
  }

  /**
   * getUrl
   *
   * Returns the URL that corresponds to the folder in which this Service's
   * handler is located.
   *
   * @return string
   */
  public function getStylesheetUrl (): string {
    return $this->handler->getStylesheetUrl();
  }

  /**
   * getDir
   *
   * Returns the filesystem path to the folder in which this Service's
   * handler is located.
   *
   * @return string
   */
  public function getStylesheetDir (): string {
    return $this->handler->getStylesheetDir();
  }
}
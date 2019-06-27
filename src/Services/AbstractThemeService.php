<?php

namespace Dashifen\WPHandler\Services;

use Dashifen\WPHandler\Handlers\Themes\AbstractThemeThemeHandler;
use Dashifen\WPHandler\Handlers\Themes\ThemeHandlerInterface;

/**
 * Class AbstractService
 *
 * @package Dashifen\WPHandler\Handlers\Services
 */
abstract class AbstractThemeService extends AbstractThemeThemeHandler {
  /**
   * @var ThemeHandlerInterface
   */
  protected $handler;

  /**
   * AbstractService constructor.
   *
   * @param ThemeHandlerInterface $handler
   */
  public function __construct(ThemeHandlerInterface $handler) {
    $this->handler = $handler;
    parent::__construct();
  }
}
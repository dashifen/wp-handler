<?php

namespace Dashifen\WPHandler\Services;

use Dashifen\WPHandler\Handlers\Plugins\AbstractPluginHandler;
use Dashifen\WPHandler\Handlers\Plugins\PluginHandlerInterface;

/**
 * Class AbstractPluginService
 *
 * The purpose of a Service is to provide a narrowly focused set of behaviors
 * that can be severed from the main Plugin code to help identify and separate
 * responsibilities.  They are, for all intents and purposes, simply other
 * Handlers, but these must be constructed with a link to the other Handler
 * that they serve.
 *
 * @package Dashifen\WPHandler\Handlers\Services
 */
abstract class AbstractPluginService extends AbstractPluginHandler {
  /**
   * @var PluginHandlerInterface
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
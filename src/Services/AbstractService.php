<?php

namespace Dashifen\WPHandler\Services;

use Dashifen\WPHandler\Handlers\AbstractHandler;
use Dashifen\WPHandler\Handlers\HandlerInterface;

/**
 * Class AbstractPluginService
 *
 * The purpose of a Service is to provide a narrowly focused set of behaviors
 * that can be severed from the main Theme or Plugin code to help identify
 * and separate responsibilities.  They are, for all intents and purposes,
 * simply other Handlers, but these must be constructed with a link to the
 * other Handler that they serve.
 *
 * @package Dashifen\WPHandler\Handlers\Services
 */
abstract class AbstractService extends AbstractHandler {
  /**
   * @var HandlerInterface
   */
  protected $handler;

  /**
   * AbstractPluginService constructor.
   *
   * @param HandlerInterface $handler
   */
  public function __construct (HandlerInterface $handler) {
    $this->handler = $handler;
  }
}
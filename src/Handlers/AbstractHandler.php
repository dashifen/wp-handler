<?php

/** @noinspection PhpUnused */

namespace Dashifen\WPHandler\Handlers;

use Throwable;
use Dashifen\WPHandler\Hooks\HookException;
use Dashifen\WPHandler\Hooks\Factory\HookFactoryInterface;
use Dashifen\WPHandler\Hooks\Collection\HookCollectionInterface;
use Dashifen\WPHandler\Hooks\Collection\HookCollectionException;

abstract class AbstractHandler implements HandlerInterface {
  /**
   * @var HookFactoryInterface
   */
  protected $hookFactory;

  /**
   * @var HookCollectionInterface
   */
  protected $hookCollection;

  /**
   * @var bool
   */
  protected $initialized = false;

  /**
   * AbstractHandler constructor.
   *
   * @param HookFactoryInterface    $hookFactory
   * @param HookCollectionInterface $hookCollection
   */
  public function __construct (
    HookFactoryInterface $hookFactory,
    HookCollectionInterface $hookCollection
  ) {
    $this->hookFactory = $hookFactory;
    $this->hookCollection = $hookCollection;
  }

  /**
   * __call
   *
   * Checks to see if the method being called is in the $hooked
   * property, and if so, calls it passing the $arguments to it.
   *
   * @param string $method
   * @param array  $arguments
   *
   * @return mixed
   * @throws HandlerException
   */
  public function __call (string $method, array $arguments) {

    // getting here should only happen via WordPress callbacks.  sure,
    // there's a bunch of other ways to do so, but callbacks are the ones
    // we care about.  so, we'll start trying to make sure that the
    // method WordPress is trying to execute is one to which it's been
    // given access; i.e., it's in our hook collection.  first step,
    // recreate the hook index for the method as if it were being
    // executed at the current action and priority.

    $action = current_action();
    $priority = has_filter($action, [$this, $method]);
    $hookIndex = $this->hookFactory->produceHookIndex($action, $this, $method, $priority);
    if ($this->hookCollection->has($hookIndex)) {

      // if we're in here, then we don't have a Hook that exactly matches
      // this method, action, and priority combination.  since we're about
      // to crash out of things anyway, we'll see if we can help the
      // programmer identify the problem.

      foreach ($this->hookCollection->getAll() as $hook) {
        if ($hook->getMethod() === $method) {

          // well, we just found a hook using this method, so the problem
          // must be that we're at the wrong action or priority.  let's see
          // if which it is.

          if ($hook->getHook() !== $action) {
            throw new HandlerException("$method is hooked but not via $action",
              HandlerException::INAPPROPRIATE_CALL);
          }

          if ($hook->getPriority() !== $priority) {
            throw new HandlerException("$method is hooked but not at $priority",
              HandlerException::INAPPROPRIATE_CALL);
          }
        }

        // if we looped over all of our hooked methods and never threw any of
        // the above exceptions, then the only remaining option is that the
        // method was never hooked the first place.  we have an exception for
        // that, too.

        throw new HandlerException("Unhooked method: $method.",
          HandlerException::UNHOOKED_METHOD);
      }
    }

    // if we made it through all that, we're good to go.  we return the
    // results of our method call because some of them might be filters and
    // not returning their results would be a problem.

    return $this->{$method}(...$arguments);
  }

  /**
   * toString
   *
   * Returns the name of this object using the late-static binding so it'll
   * return the name of the concrete handler, not simply "AbstractHandler."
   *
   * @return string
   */
  public function __toString (): string {
    return static::class;
  }

  /**
   * initialize
   *
   * Uses addAction() and addFilter() to connect WordPress to the methods
   * of this object's child which are intended to be protected.
   *
   * @return void
   */
  abstract public function initialize (): void;

  /**
   * getHookFactory
   *
   * Returns the hook factory property.
   *
   * @return HookFactoryInterface
   */
  public function getHookFactory (): HookFactoryInterface {
    return $this->hookFactory;
  }

  /**
   * getHookCollection
   *
   * Returns the hook collection property.
   *
   * @return HookCollectionInterface
   */
  public function getHookCollection (): HookCollectionInterface {
    return $this->hookCollection;
  }

  /**
   * isInitialized
   *
   * Returns the value of the initialized property at the start of the method
   * but also sets that value to true.  This function should be called when
   * initializing handlers if you need to avoid re-initialization problems.
   *
   * @return bool
   */
  final protected function isInitialized (): bool {
    $returnValue = $this->initialized;
    $this->initialized = true;
    return $returnValue;
  }

  /**
   * addAction
   *
   * Passes its arguments to add_action() and adds $method to the
   * $hooked property.
   *
   * @param string $hook
   * @param string $method
   * @param int    $priority
   * @param int    $arguments
   *
   * @return string
   * @throws HookException
   * @throws HookCollectionException
   */
  protected function addAction (string $hook, string $method, int $priority = 10, int $arguments = 1): string {
    $hookIndex = $this->hookFactory->produceHookIndex($hook, $this, $method, $priority);
    $this->hookCollection->set($hookIndex, $this->hookFactory->produceHook($hook, $this, $method, $priority, $arguments));
    return add_action($hook, [$this, $method], $priority, $arguments);
  }

  /**
   * removeAction
   *
   * Removes a hooked method from WP core and the record of the hook
   * from our $hooked properties.
   *
   * @param string $hook
   * @param string $method
   * @param int    $priority
   *
   * @return bool
   */
  protected function removeAction (string $hook, string $method, int $priority = 10): bool {
    $this->hookCollection->reset($this->hookFactory->produceHookIndex($hook, $this, $method, $priority));
    return remove_action($hook, [$this, $method], $priority);
  }

  /**
   * addFilter
   *
   * Passes its arguments to add_filter() and adds $method to  the
   * $hooked property.
   *
   * @param string $hook
   * @param string $method
   * @param int    $priority
   * @param int    $arguments
   *
   * @return string
   * @throws HookException
   * @throws HookCollectionException
   */
  protected function addFilter (string $hook, string $method, int $priority = 10, int $arguments = 1): string {
    $hookIndex = $this->hookFactory->produceHookIndex($hook, $this, $method, $priority);
    $this->hookCollection->set($hookIndex, $this->hookFactory->produceHook($hook, $this, $method, $priority, $arguments));
    return add_filter($hook, [$this, $method], $priority, $arguments);
  }

  /**
   * removeFilter
   *
   * Removes a filter from WP and the record of the hooked method
   * from the $hooked property.
   *
   * @param string $hook
   * @param string $method
   * @param int    $priority
   *
   * @return bool
   */
  protected function removeFilter (string $hook, string $method, int $priority = 10): bool {
    $this->hookCollection->reset($this->hookFactory->produceHookIndex($hook, $this, $method, $priority));
    return remove_filter($hook, [$this, $method], $priority);
  }

  /**
   * debug
   *
   * Given stuff, print information about it and then die() if
   * the $die flag is set.
   *
   * @param mixed $stuff
   * @param bool  $die
   *
   * @return void
   */
  public static function debug ($stuff, $die = false): void {
    if (!self::isDebug()) {

      // this return ensures that we don't print debugging statements
      // on installations where WP debugging is turned off.

      return;
    }

    $message = "<pre>" . print_r($stuff, true) . "</pre>";

    if (!$die) {
      echo $message;
      return;
    }

    die($message);
  }

  /**
   * isDebug
   *
   * Returns true when WP_DEBUG exists and is set.
   *
   * @return bool
   */
  public static function isDebug (): bool {
    return defined("WP_DEBUG") && WP_DEBUG;
  }

  /**
   * writeLog
   *
   * Calling this method should write $data to the WordPress debug.log file.
   *
   * @param mixed $data
   *
   * @return void
   */
  public static function writeLog ($data): void {

    // source:  https://www.elegantthemes.com/blog/tips-tricks/using-the-wordpress-debug-log
    // accessed:  2018-07-09

    if (!function_exists("write_log")) {
      function write_log ($log) {
        if (is_array($log) || is_object($log)) {
          error_log(print_r($log, true));
        } else {
          error_log($log);
        }
      }
    }

    write_log($data);
  }

  /**
   * catcher
   *
   * This serves as a general-purpose Exception handler which displays
   * the caught object when we're debugging and writes it to the log when
   * we're not.
   *
   * @param Throwable $thrown
   *
   * @return void
   */
  public static function catcher (Throwable $thrown): void {
    self::isDebug() ? self::debug($thrown, true) : self::writeLog($thrown);
  }
}

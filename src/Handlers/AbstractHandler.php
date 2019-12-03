<?php

/** @noinspection PhpUnused */

namespace Dashifen\WPHandler\Handlers;

use Throwable;
use Dashifen\WPHandler\Hooks\HookException;
use Dashifen\WPHandler\Hooks\Factory\HookFactoryInterface;
use Dashifen\WPHandler\Hooks\Collection\HookCollectionInterface;
use Dashifen\WPHandler\Hooks\Collection\HookCollectionException;
use Dashifen\WPHandler\Agents\Collection\AgentCollectionInterface;
use Dashifen\WPHandler\Agents\Collection\AgentCollectionException;
use Dashifen\WPHandler\Hooks\Collection\Factory\HookCollectionFactoryInterface;
use Dashifen\WPHandler\Agents\Collection\Factory\AgentCollectionFactoryInterface;

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
   * @var HookCollectionFactoryInterface
   */
  protected $hookCollectionFactory;

  /**
   * @var AgentCollectionInterface
   */
  protected $agentCollection;

  /**
   * @var bool
   */
  protected $initialized = false;

  /**
   * AbstractHandler constructor.
   *
   * @param HookFactoryInterface           $hookFactory
   * @param HookCollectionFactoryInterface $hookCollectionFactory
   */
  public function __construct (
    HookFactoryInterface $hookFactory,
    HookCollectionFactoryInterface $hookCollectionFactory
  ) {
    $this->hookFactory = $hookFactory;

    // from this abstract class descends all of our Handlers and Agents, each
    // of which should have their own hook collection, we don't pass around the
    // collection itself, we pass the factory which makes them.  that way,
    // every Handler and all of its Agents gets their own collection rather
    // than trying to share a single one.  then, we store the factory locally,
    // too, because any Agents this Handler employs will need it, too.

    $this->hookCollection = $hookCollectionFactory->produceHookCollection();
    $this->hookCollectionFactory = $hookCollectionFactory;
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

    // getting here should only happen via WordPress callbacks.  sure, there's
    // a bunch of other ways to do so, but callbacks are the ones we care
    // about.  so, we'll start trying to make sure that the method WordPress is
    // trying to execute is one to which it's been given access; i.e., it's in
    // our hook collection.  first step, recreate the hook index for the method
    // as if it were being executed at the current action and priority.

    $action = current_action();

    if (empty($action)) {
      throw new HandlerException(
        "Unable to determine action/filter at which $method was called",
        HandlerException::INAPPROPRIATE_CALL
      );
    }

    $priority = has_filter($action, [$this, $method]);
    $hookIndex = $this->hookFactory->produceHookIndex($action, $this, $method, $priority);
    if (!$this->hookCollection->has($hookIndex)) {

      // if we're in here, then we don't have a Hook that exactly matches
      // this method, action, and priority combination.  since we're about
      // to crash out of things anyway, we'll see if we can help the
      // programmer identify the problem.

      foreach ($this->hookCollection->getAll() as $hook) {
        if ($hook->method === $method) {

          // well, we just found a hook using this method, so the problem
          // must be that we're at the wrong action or priority.  let's see
          // if which it is.

          if ($hook->hook !== $action) {
            throw new HandlerException("$method is hooked but not via $action",
              HandlerException::INAPPROPRIATE_CALL);
          }

          if ($hook->priority !== $priority) {
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
    // not returning their results would be a problem.  before we do so, we
    // don't want to send a method anything it's not expecting.  so, we'll
    // make sure to slice off any additional arguments that ended up here
    // (due to the variadic nature of this method) before we unpack them
    // and send them over there.  notice we don't care if the number of
    // expected arguments is greater than what we have; in that case, it's
    // likely an error, and we'll handle it elsewhere.  this is similar to
    // the work done by WP Core in the WP_Hook::apply_filters() method.

    $hook = $this->hookCollection->get($hookIndex);
    if ($hook->argumentCount < sizeof($arguments)) {
      $arguments = array_slice($arguments, 0, $hook->argumentCount);
    }

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
   * getHookCollection
   *
   * Returns the hook collection factory property.
   *
   * @return HookCollectionFactoryInterface
   */
  public function getHookCollectionFactory (): HookCollectionFactoryInterface {
    return $this->hookCollectionFactory;
  }

  /**
   * getAgentCollection
   *
   * In the unlikely event that an external scope needs a reference to this
   * Handler's agent collection, this returns that property.
   *
   * @return AgentCollectionInterface
   */
  public function getAgentCollection (): AgentCollectionInterface {
    return $this->agentCollection;
  }

  /**
   * setAgentCollection
   *
   * Given an agent collection factory, produces an agent collection and
   * saves it in our properties.
   *
   * @param AgentCollectionFactoryInterface $agentCollectionFactory
   *
   * @return void
   * @throws AgentCollectionException
   */
  public function setAgentCollection (AgentCollectionFactoryInterface $agentCollectionFactory): void {

    // and this is why we have a setter for our agent collection and don't
    // define an agent collection factory as a dependency of our constructor:
    // the factory needs to know who the handler will be for its agents.

    $this->agentCollection = $agentCollectionFactory->produceAgentCollection($this);
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
   * initializeAgents
   *
   * This is merely an opinionated suggestion for how a Handler with Agents
   * might initialize them.  Concrete extensions of this object are free to
   * use, extend, or ignore this one as they see fit.
   *
   * @return void
   */
  protected function initializeAgents (): void {

    // our agent collection implements the Iterable interface so we can
    // use a foreach to loop over each of the Agents that it has set within
    // it's internal array.  then, we just call their initialize methods
    // in sequence.

    if ($this->agentCollection instanceof AgentCollectionInterface) {
      foreach ($this->agentCollection->getAll() as $agent) {
        $agent->initialize();
      }
    }
  }

  /**
   * addAction
   *
   * Passes its arguments to add_action() and adds a Hook to our collection.
   *
   * @param string $hook
   * @param string $method
   * @param int    $priority
   * @param int    $arguments
   *
   * @return string
   * @throws HandlerException
   */
  protected function addAction (string $hook, string $method, int $priority = 10, int $arguments = 1): string {
    $this->addHookToCollection($hook, $method, $priority, $arguments);
    return add_action($hook, [$this, $method], $priority, $arguments);
  }

  /**
   * addHookToCollection
   *
   * Given data about a hook, produces one and add it to our collection.
   *
   * @param string $hook
   * @param string $method
   * @param int    $priority
   * @param int    $arguments
   *
   * @return void
   * @throws HandlerException
   */
  private function addHookToCollection (string $hook, string $method, int $priority, int $arguments): void {
    $hookIndex = $this->hookFactory->produceHookIndex($hook, $this, $method, $priority);

    try {
      $this->hookCollection->set($hookIndex, $this->hookFactory->produceHook($hook, $this, $method, $priority, $arguments));
    } catch (HookCollectionException | HookException $exception) {

      // to make things easier on the calling scope, we'll "merge" the two
      // types of exceptions thrown by the hook collection here into a single
      // type:  our HandlerException.

      throw new HandlerException(
        $exception->getMessage(),
        HandlerException::FAILURE_TO_HOOK,
        $exception
      );
    }
  }

  /**
   * removeAction
   *
   * Removes a hooked method from WP core and the record of the hook from our
   * collection.
   *
   * @param string $hook
   * @param string $method
   * @param int    $priority
   *
   * @return bool
   */
  protected function removeAction (string $hook, string $method, int $priority = 10): bool {
    $this->removeHookFromCollection($hook, $method, $priority);
    return remove_action($hook, [$this, $method], $priority);
  }

  /**
   * removeHookFromCollection
   *
   * Given the information about a hook in our collection, removes it.
   *
   * @param string $hook
   * @param string $method
   * @param int    $priority
   *
   * @return void
   */
  private function removeHookFromCollection (string $hook, string $method, int $priority): void {
    $hookIndex = $this->hookFactory->produceHookIndex($hook, $this, $method, $priority);
    $this->hookCollection->reset($hookIndex);
  }

  /**
   * addFilter
   *
   * Passes its arguments to add_filter() and adds a Hook to our collection.
   *
   * @param string $hook
   * @param string $method
   * @param int    $priority
   * @param int    $arguments
   *
   * @return string
   * @throws HandlerException
   */
  protected function addFilter (string $hook, string $method, int $priority = 10, int $arguments = 1): string {
    $this->addHookToCollection($hook, $method, $priority, $arguments);
    return add_filter($hook, [$this, $method], $priority, $arguments);
  }

  /**
   * removeFilter
   *
   * Removes a filter from WP and the record of the Hook from our collection.
   *
   * @param string $hook
   * @param string $method
   * @param int    $priority
   *
   * @return bool
   */
  protected function removeFilter (string $hook, string $method, int $priority = 10): bool {
    $this->removeHookFromCollection($hook, $method, $priority);
    return remove_filter($hook, [$this, $method], $priority);
  }

  /**
   * debug
   *
   * Given stuff, print information about it and then die() if the $die flag is
   * set.  Typically, this only works when the isDebug() method returns true,
   * but the $force parameter will override this behavior.
   *
   * @param mixed $stuff
   * @param bool  $die
   * @param bool  $force
   *
   * @return void
   */
  public static function debug ($stuff, bool $die = false, bool $force = false): void {
    if (self::isDebug() || $force) {
      $message = "<pre>" . print_r($stuff, true) . "</pre>";

      if (!$die) {
        echo $message;
        return;
      }

      die($message);
    }
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

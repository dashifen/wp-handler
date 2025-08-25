<?php

/** @noinspection PhpUnused */

namespace Dashifen\WPHandler\Handlers;

use Closure;
use ReflectionClass;
use ReflectionMethod;
use ReflectionFunction;
use ReflectionException;
use Dashifen\WPDebugging\WPDebuggingTrait;
use Dashifen\WPHandler\Hooks\HookException;
use Dashifen\WPHandler\Hooks\Factory\HookFactory;
use Dashifen\WPHandler\Hooks\Factory\HookFactoryInterface;
use Dashifen\WPHandler\Hooks\Collection\HookCollectionInterface;
use Dashifen\WPHandler\Agents\Collection\AgentCollectionInterface;
use Dashifen\WPHandler\Agents\Collection\AgentCollectionException;
use Dashifen\WPHandler\Hooks\Collection\Factory\HookCollectionFactory;
use Dashifen\WPHandler\Hooks\Collection\Factory\HookCollectionFactoryInterface;
use Dashifen\WPHandler\Agents\Collection\Factory\AgentCollectionFactoryInterface;

abstract class AbstractHandler implements HandlerInterface
{
  use WPDebuggingTrait;
  
  protected HookFactoryInterface $hookFactory;
  protected HookCollectionInterface $hookCollection;
  protected HookCollectionFactoryInterface $hookCollectionFactory;
  protected AgentCollectionInterface $agentCollection;
  protected ReflectionClass $handlerReflection;
  protected bool $initialized = false;
  private array $reflectionMethods = [];
  
  /**
   * AbstractHandler constructor.
   *
   * @param HookFactoryInterface|null           $hookFactory
   * @param HookCollectionFactoryInterface|null $hookCollectionFactory
   */
  public function __construct(
    ?HookFactoryInterface           $hookFactory = null,
    ?HookCollectionFactoryInterface $hookCollectionFactory = null
  ) {
    // likely, the vast majority of situations will not require behaviors other
    // than those that are defined in the HookFactory and HookCollectionFactory
    // objects.  therefore, we default our parameters to null and, if we still
    // have nulls herein, we use the null coalescing operator to construct the
    // default objects as needed.
    
    $this->hookFactory = $hookFactory ?? new HookFactory();
    
    // from this abstract class descends all of our Handlers and Agents, each
    // of which should have their own hook collection, we don't pass around the
    // collection itself, we pass the factory which makes them.  that way,
    // every Handler and all of its Agents gets their own collection rather
    // than trying to share a single one.  then, we store the factory locally,
    // too, because any Agents this Handler employs will need it, too.
    
    $hookCollectionFactory = $hookCollectionFactory ?? new HookCollectionFactory();
    $this->hookCollection = $hookCollectionFactory->produceHookCollection();
    $this->hookCollectionFactory = $hookCollectionFactory;
    $this->handlerReflection = new ReflectionClass($this);
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
  public function __call(string $method, array $arguments)
  {
    // getting here should only happen via WordPress callbacks and only for
    // MethodHooks;  closures aren't a part of an object so they won't ever get
    // called like this.  once we get here, we want to see if there's a method
    // in our hook collection that corresponds to this action and priority.
    
    $action = current_action();
    
    if (empty($action)) {
      throw new HandlerException(
        "Unable to determine action/filter at which $method was called",
        HandlerException::INAPPROPRIATE_CALL
      );
    }
    
    $priority = has_filter($action, [$this, $method]);
    $hookIndex = $this->hookFactory->produceHookIndex($action, $this, $method, $priority);
    if (!$this->hookCollection[$hookIndex]) {
      // if we're in here, then we don't have a Hook that exactly matches
      // this method, action, and priority combination.  since we're about
      // to crash out of things anyway, we'll see if we can help the
      // programmer identify the problem.
      
      foreach ($this->hookCollection as $hook) {
        if ($hook->method === $method) {
          
          // well, we just found a hook using this method, so the problem
          // must be that we're at the wrong action or priority.  let's see
          // which it is.
          
          if ($hook->hook !== $action) {
            throw new HandlerException(
              "$method is hooked but not via $action",
              HandlerException::INAPPROPRIATE_CALL
            );
          }
          
          if ($hook->priority !== $priority) {
            throw new HandlerException(
              "$method is hooked but not at $priority",
              HandlerException::INAPPROPRIATE_CALL
            );
          }
        }
        
        // if we looped over all of our hooked methods and never threw any of
        // the above exceptions, then the only remaining option is that the
        // method was never hooked the first place.  we have an exception for
        // that, too.
        
        throw new HandlerException(
          "Unhooked method: $method.",
          HandlerException::UNHOOKED_METHOD
        );
      }
    }
    
    // in keeping with WP Core's WP_Hook::apply_filters method, we want to
    // remove any extra arguments before passing them over.  this is not
    // likely too problematic unless we have a variadic method that might
    // do something to/with unexpected parameters.  so, before we call our
    // method, we use array_slice to remove extra arguments.
    
    $hook = $this->hookCollection[$hookIndex];
    $arguments = array_slice($arguments, 0, $hook->argumentCount);
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
  public function __toString(): string
  {
    return static::class;
  }
  
  /**
   * initialize
   *
   * Uses addAction and/or addFilter to attach protected methods of this object
   * to the ecosystem of WordPress action and filter hooks.
   *
   * @return void
   */
  abstract public function initialize(): void;
  
  /**
   * getHookFactory
   *
   * Returns the hook factory property.
   *
   * @return HookFactoryInterface
   */
  public function getHookFactory(): HookFactoryInterface
  {
    return $this->hookFactory;
  }
  
  /**
   * getHookCollection
   *
   * Returns the hook collection property.
   *
   * @return HookCollectionInterface
   */
  public function getHookCollection(): HookCollectionInterface
  {
    return $this->hookCollection;
  }
  
  /**
   * getHookCollection
   *
   * Returns the hook collection factory property.
   *
   * @return HookCollectionFactoryInterface
   */
  public function getHookCollectionFactory(): HookCollectionFactoryInterface
  {
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
  public function getAgentCollection(): AgentCollectionInterface
  {
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
  public function setAgentCollection(AgentCollectionFactoryInterface $agentCollectionFactory): void
  {
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
  final protected function isInitialized(): bool
  {
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
  protected function initializeAgents(): void
  {
    // our agent collection implements the Iterable interface so we can
    // use a foreach to loop over each of the Agents that it has set within
    // it's internal array.  then, we just call their initialize methods
    // in sequence.
    
    if (isset($this->agentCollection)) {
      foreach ($this->agentCollection as $agent) {
        $agent->initialize();
      }
    }
  }
  
  /**
   * addAction
   *
   * Passes its arguments to add_action() and adds a Hook to our collection.
   *
   * @param string         $hook
   * @param string|Closure $callback
   * @param int            $priority
   * @param int            $arguments
   *
   * @return string
   * @throws HandlerException
   */
  protected function addAction(string         $hook,
                               string|Closure $callback,
                               int            $priority = 10,
                               int            $arguments = 1
  ): string {
    if (!$this->isValidCallback($callback, $arguments)) {
      throw new HandlerException(
        'Callback expects more arguments than ' . $arguments . '.',
        HandlerException::INVALID_CALLBACK
      );
    }
    
    $this->addHookToCollection($hook, $callback, $priority, $arguments);
    
    // if $callback is a string, then we need to add our action to WP using
    // the array syntax for method calls.  otherwise, we can just pass it
    // over to add_action since it is, itself, callable.
    
    return is_string($callback)
      ? add_action($hook, [$this, $callback], $priority, $arguments)
      : add_action($hook, $callback, $priority, $arguments);
  }
  
  /**
   * isValidCallback
   *
   * Given a callback, returns true if it's callable and if the number
   * of arguments we're going to pass it is greater than or equal to the
   * number of its required parameters.
   *
   * @param string|Closure $callback
   * @param int            $arguments
   *
   * @return bool
   * @throws HandlerException
   */
  protected function isValidCallback(string|Closure $callback, int $arguments): bool
  {
    try {
      // note: the getReflectionMethod may through a HandlerException if the
      // $callback either doesn't exist or is a private method.  we catch that
      // exception in the calling scope.  the ReflectionFunction constructor
      // can throw a ReflectionException; we catch that one here and re-throw
      // our own.
      
      $reflection = !($callback instanceof Closure)
        ? $this->getReflectionMethod($callback)
        : new ReflectionFunction($callback);
      
      // now that we've reflected either the $callback Closure or a method of
      // this object, we need to confirm that $arguments is greater than or
      // equal to the number of required parameters.  it's okay to exceed the
      // requirement because we assume that means a dev wants to override a
      // default value.
      
      return $arguments >= $reflection->getNumberOfRequiredParameters();
    } catch (ReflectionException $e) {
      
      // if we're here, the exception must have been thrown from within the
      // ReflectionFunction constructor.  any other ReflectionException is
      // caught and "converted" into our own HandlerException.  these are
      // caught elsewhere.
      
      throw new HandlerException(
        'Invalid callback.',
        HandlerException::INVALID_CALLBACK,
        $e
      );
    }
  }
  
  /**
   * getReflectionMethod
   *
   * Returns a ReflectionMethod object for a non-private method within
   * this object or throws an exception if the method doesn't exist or
   * is private.
   *
   * @param string $callback
   *
   * @return ReflectionMethod
   * @throws HandlerException
   */
  private function getReflectionMethod(string $callback): ReflectionMethod
  {
    // to try and save a bit of time, just in case this callback was already
    // reflected, we've stored previously instantiated objects in memory.  if
    // it's not already there, then we'll create it within the if-block and
    // return it thereafter.
    
    try {
      if (!isset($this->reflectionMethods[$callback])) {
        $this->reflectionMethods[$callback] = $this->handlerReflection->getMethod($callback);
      }
      
      // now that we know our ReflectionMethod is found within our property,
      // we'll see if it's private.  if it is, we throw a HandlerException
      // which will be handled elsewhere regarding that fact.
      
      if ($this->reflectionMethods[$callback]->isPrivate()) {
        throw new HandlerException(
          'Invalid callback: ' . $callback . ' is private.',
          HandlerException::INVALID_CALLBACK,
        );
      }
      
    } catch (ReflectionException $e) {
      
      // if we catch a ReflectionException, it will have been through from
      // within the getMethod call above.  this indicates that $callback is not
      // a method of our object, and we'll tell the calling scope about that
      // fact by re-throwing one of our own exceptions here.
      
      throw new HandlerException(
        'Invalid callback: unknown method, ' . $callback . '.',
        HandlerException::INVALID_CALLBACK,
        $e
      );
    }
    
    return $this->reflectionMethods[$callback];
  }
  
  /**
   * addHookToCollection
   *
   * Given data about a hook, produces one and add it to our collection.
   *
   * @param string         $hook
   * @param string|Closure $callback
   * @param int            $priority
   * @param int            $arguments
   *
   * @return void
   * @throws HandlerException
   */
  private function addHookToCollection(string         $hook,
                                       string|Closure $callback,
                                       int            $priority,
                                       int            $arguments
  ): void {
    try {
      // to add a hook to our collection, we need the index it'll use therein
      // and the actually HookInterface implementation that we store.  we make
      // those and then pass them to our collection's set method.
      
      $hookIndex = $this->hookFactory->produceHookIndex($hook, $this, $callback, $priority);
      $hookObject = $this->hookFactory->produceHook($hook, $this, $callback, $priority, $arguments);
      $this->hookCollection[$hookIndex] = $hookObject;
    } catch (HookException $exception) {
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
   * collection.  Note:  closures cannot be removed at this time because they
   * cannot be removed using WP's remove_action.
   *
   * @param string $hook
   * @param string $method
   * @param int    $priority
   *
   * @return bool
   */
  protected function removeAction(string $hook, string $method, int $priority = 10): bool
  {
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
  private function removeHookFromCollection(string $hook, string $method, int $priority): void
  {
    $hookIndex = $this->hookFactory->produceHookIndex($hook, $this, $method, $priority);
    unset($this->hookCollection[$hookIndex]);
  }
  
  /**
   * addFilter
   *
   * Passes its arguments to add_filter and adds a Hook to our collection.
   *
   * @param string         $hook
   * @param string|Closure $callback
   * @param int            $priority
   * @param int            $arguments
   *
   * @return string
   * @throws HandlerException
   */
  protected function addFilter(string         $hook,
                               string|Closure $callback,
                               int            $priority = 10,
                               int            $arguments = 1
  ): string {
    if (!$this->isValidCallback($callback, $arguments)) {
      throw new HandlerException(
        'Callback expects more arguments than ' . $arguments . '.',
        HandlerException::INVALID_CALLBACK
      );
    }
    
    $this->addHookToCollection($hook, $callback, $priority, $arguments);
    
    // based on the type of $callback, we can handle the arguments to the WP
    // add_filter function like we did in addAction above.
    
    return is_string($callback)
      ? add_filter($hook, [$this, $callback], $priority, $arguments)
      : add_filter($hook, $callback, $priority, $arguments);
  }
  
  /**
   * removeFilter
   *
   * Removes a filter from WP and the record of the Hook from our collection.
   * Note:  closures cannot be removed at this time because closures cannot be
   * removed using WP's remove_filter.
   *
   * @param string $hook
   * @param string $method
   * @param int    $priority
   *
   * @return bool
   */
  protected function removeFilter(string $hook, string $method, int $priority = 10): bool
  {
    $this->removeHookFromCollection($hook, $method, $priority);
    return remove_filter($hook, [$this, $method], $priority);
  }
}

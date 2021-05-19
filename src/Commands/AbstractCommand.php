<?php

namespace Dashifen\WPHandler\Commands;

use Closure;
use Dashifen\WPHandler\Agents\AbstractAgent;
use Dashifen\WPHandler\Handlers\HandlerInterface;
use Dashifen\WPHandler\Repositories\Arguments\ArgumentInterface;
use Dashifen\WPHandler\Commands\Arguments\Collection\ArgumentCollection;
use Dashifen\WPHandler\Commands\Arguments\Collection\ArgumentCollectionInterface;

abstract class AbstractCommand extends AbstractAgent implements CommandInterface
{
  protected string $name;
  protected string $slug;
  protected string $shortDesc;
  protected ?Closure $beforeInvoke = null;
  protected ?Closure $afterInvoke = null;
  protected string $longDesc = '';
  protected string $when = 'after_wp_load';
  protected ArgumentCollectionInterface $arguments;
  protected bool $isDeferred = false;
  
  /**
   * AbstractPluginService constructor.
   *
   * @param HandlerInterface                 $handler
   * @param ArgumentCollectionInterface|null $arguments
   */
  public function __construct(HandlerInterface $handler, ?ArgumentCollectionInterface $arguments = null)
  {
    $this->arguments = $arguments ?? new ArgumentCollection();
    parent::__construct($handler);
  }
  
  /**
   * initialize
   *
   * Uses addAction and/or addFilter to attach protected methods of this object
   * to the ecosystem of WordPress action and filter hooks.
   *
   * @return void
   */
  public function initialize(): void
  {
    // it's likely that commands won't need this method all that much since
    // they're "initialized" via the WP_CLI::add_command method instead of this
    // one.  therefore, we stub it here but extensions can always override the
    // stub if they need to.
  }
  
  
  /**
   * __get
   *
   * Returns the value of any of the above listed properties.
   *
   * @param string $property
   *
   * @return mixed
   * @throws CommandException
   */
  public function __get(string $property)
  {
    if (!property_exists($this, $property)) {
      throw new CommandException(
        'Unknown property: ' . $property,
        CommandException::UNKNOWN_PROPERTY
      );
    }
    
    return $this->$property;
  }
  
  /**
   * addArgument
   *
   * Adds an argument synopsis to this command agent's argument collection.
   *
   * @param ArgumentInterface $argument
   *
   * @return void
   */
  public function addArgument(ArgumentInterface $argument): void
  {
    $this->arguments[$argument->name] = $argument;
  }
  
  /**
   * getCallable
   *
   * Returns a callable function that is run at the time the CLI command is
   * executed to complete the work of the command.
   *
   * @return callable
   */
  public function getCallable(): callable
  {
    // WordPress will execute this callable when the command line tells it to
    // and pass the command line arguments and flags to the callable.  we, in
    // turn, pass those parameters into the method below.  this allows WP Core
    // to reference our protected execute method and, in turn, allows that
    // method to remain a part of the handler/agent ecosystem.
    
    return fn(array $args, array $flags) => $this->execute($args, $flags);
  }
  
  /**
   * execute
   *
   * Performs the behaviors of this command.
   *
   * @param array $args
   * @param array $flags
   *
   * @return void
   */
  abstract protected function execute(array $args, array $flags): void;
  
  /**
   * getCommandDescription
   *
   * Returns the full description of the command this agent performs for use
   * as the third parameter to the WP_CLI add_command method.
   *
   * @return array
   */
  public function getDescription(): array
  {
    // we construct this array using the keywords defined in the WP_CLI
    // add_command docs which don't exactly match our property names.  notice
    // that we don't include the command's name.  that's because it becomes the
    // first parameter of the add_command call; this array is the third.
    
    $description = [
      'before_invoke' => $this->beforeInvoke,
      'after_invoke'  => $this->afterInvoke,
      'shortdesc'     => $this->shortDesc,
      'longdesc'      => $this->longDesc,
      'synopsis'      => $this->arguments->getCollection(),
      'when'          => $this->when,
      'is_deferred'   => $this->isDeferred,
    ];
    
    return array_filter($description);
  }
  
  /**
   * setName
   *
   * Sets the name and slug properties.
   *
   * @param string $name
   *
   * @return void
   */
  public function setName(string $name): void
  {
    $this->slug = sanitize_title($name);
    $this->name = $name;
  }
  
  /**
   * setShortDesc
   *
   * Sets the short description property.
   *
   * @param string $shortDesc
   *
   * @return void
   * @throws CommandException
   */
  public function setShortDesc(string $shortDesc): void
  {
    // the docs for the WP_CLI::add_command function specify that short
    // descriptions are supposed to be less than 80 characters.  if this one is
    // too long, we'll throw an exception and the developers can fix it.
    
    if (strlen($shortDesc) > 80) {
      throw new CommandException(
        'Short description is too long; 80 characters or less, please.',
        CommandException::INVALID_VALUE
      );
    }
    
    $this->shortDesc = $shortDesc;
  }
  
  /**
   * setBeforeInvoke
   *
   * Sets the before invoke property defining behaviors that are executed
   * right before the actual command is run.
   *
   * @param Closure|null $beforeInvoke
   *
   * @return void
   */
  public function setBeforeInvoke(?Closure $beforeInvoke): void
  {
    $this->beforeInvoke = $beforeInvoke;
  }
  
  /**
   * setAfterInvoke
   *
   * Sets the after invoke property defining behaviors that are executed
   * right after the actual command is run.
   *
   * @param Closure|null $afterInvoke
   *
   * @return void
   */
  public function setAfterInvoke(?Closure $afterInvoke): void
  {
    $this->afterInvoke = $afterInvoke;
  }
  
  /**
   * setLongDesc
   *
   * Sets the long description property.
   *
   * @param string $longDesc
   *
   * @return void
   */
  public function setLongDesc(string $longDesc): void
  {
    $this->longDesc = $longDesc;
  }
  
  /**
   * setWhen
   *
   * Sets the when property.
   *
   * @param string $when
   *
   * @return void
   */
  public function setWhen(string $when): void
  {
    // the default list of hooks can be found in the command cookbook here:
    // https://make.wordpress.org/cli/handbook/references/internal-api/wp-cli-add-hook/#notes
    // we don't limit our when parameter to only the ones in that list because
    // commands can add their own hooks via the WP_CLI::do_hook() function.
    
    $this->when = $when;
  }
  
  /**
   * setIsDeferred
   *
   * Sets the property determining whether or not this command has been
   * deferred.  Honestly: the docs don't really tell us what this is, but for
   * the sake of being complete, we've included it.
   *
   * @param bool $isDeferred
   */
  public function setIsDeferred(bool $isDeferred): void
  {
    $this->isDeferred = $isDeferred;
  }
}

<?php

namespace Dashifen\WPHandler\Repositories\Arguments;

use Dashifen\Repository\Repository;
use Dashifen\Repository\RepositoryException;

abstract class AbstractArgument extends Repository implements ArgumentInterface
{
  protected string $type;             // positional, assoc, or flag
  protected string $name;             // name of the argument this synopsis describes
  protected string $description = ''; // longer description of what the argument does
  protected array $options = [];      // set of viable options (for type = assoc)
  protected string $default = '';     // default value for it (for type = assoc)
  protected bool $repeating = false;  // can it repeat?  (e.g. plugin install a b c)
  protected bool $optional;           // is it optional  (i.e. type != positional)
  
  /**
   * AbstractSynopsis constructor.
   *
   * Passes the $data array up to our parent and then makes sure that the
   * relationship between the default and options properties is appropriate..
   *
   * @param array $data
   *
   * @throws RepositoryException
   * @throws ArgumentException
   */
  public function __construct(array $data = [])
  {
    parent::__construct($data);
    $hasDefault = !empty($this->default);
    $hasOptions = !empty($this->options);
    if ($hasDefault || $hasOptions) {
      
      // now that we know we have one or the other (or both), we need to see
      // if our default and options properties are valid.
      
      if (!$hasDefault && $hasOptions) {
        
        // if we options but the programmer didn't specify a default value,
        // we'll make the opinionated choice to use the first one as that
        // value.  if they don't like it, then they should have told us
        // otherwise!
        
        $this->default = $this->options[0];
      } elseif ($hasDefault && !$hasOptions) {
        
        // if we have a default value but no options, we could wonder as to
        // why they set things up this way, but we can at least prevent WP from
        // yelling about it by making our list of options an array of the
        // single value we do have.
        
        $this->options = [$this->default];
      } elseif (!in_array($this->default, $this->options)) {
        
        // and here's the only real problem we can't solve here:  if we have
        // both a default and options but the former is not contained in the
        // latter, then we just have to throw an exception and let a dev fix
        // it.
        
        throw new ArgumentException(
          'Invalid default: ' . $this->default,
          ArgumentException::INVALID_DEFAULT
        );
      }
    }
  }
  
  /**
   * setType
   *
   * Sets the type property.
   *
   * @param string $type
   *
   * @return void
   * @throws ArgumentException
   */
  protected function setType(string $type): void
  {
    if (!in_array($type, ['positional', 'assoc', 'flag'])) {
      throw new ArgumentException(
        'Invalid type: ' . $type,
        ArgumentException::INVALID_TYPE
      );
    }
    
    $this->type = $type;
  }
  
  /**
   * setName
   *
   * Sets the name property.
   *
   * @param string $name
   *
   * @return void
   */
  protected function setName(string $name): void
  {
    $this->name = $name;
  }
  
  /**
   * setDescription
   *
   * Sets the description property.
   *
   * @param string $description
   *
   * @return void
   */
  protected function setDescription(string $description): void
  {
    $this->description = $description;
  }
  
  /**
   * setDefault
   *
   * Sets the default property.
   *
   * @param string $default
   *
   * @return void
   */
  protected function setDefault(string $default): void
  {
    $this->default = $default;
  }
  
  /**
   * setOptions
   *
   * Sets the options property.
   *
   * @param array $options
   *
   * @return void
   */
  protected function setOptions(array $options): void
  {
    $this->options = $options;
  }
  
  /**
   * setRepeating
   *
   * Sets the repeating property.
   *
   * @param bool $repeating
   *
   * @return void
   */
  protected function setRepeating(bool $repeating): void
  {
    $this->repeating = $repeating;
  }
  
  /**
   * setOptional
   *
   * Sets the optional property.
   *
   * @param bool $optional
   *
   * @return void
   */
  protected function setOptional(bool $optional): void
  {
    $this->optional = $optional;
  }
}

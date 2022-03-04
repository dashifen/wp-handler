<?php

namespace Dashifen\WPHandler\Commands\Collection;

use Dashifen\Collection\AbstractCollection;
use Dashifen\WPHandler\Commands\CommandInterface;

class CommandCollection extends AbstractCollection implements CommandCollectionInterface
{
  /**
   * @var CommandInterface[]
   */
  protected array $collection = [];
  
  /**
   * getCollection
   *
   * Returns the entire collection but now with a type hint so that IDEs may
   * recognize the contents of the array without additional hinting.
   *
   * @return CommandInterface[]
   */
  public function getCollection(): array
  {
    return parent::getCollection();
  }
  
  /**
   * current
   *
   * Returns the value at the current index in our collection which we know
   * will a CommandInterface implementation so we can override our type hinting
   * to make it more specific here.
   *
   * @return CommandInterface
   */
  public function current(): CommandInterface
  {
    return parent::current();
  }
  
  /**
   * offsetGet
   *
   * Returns the value at the specified index within the collection.  As with
   * the other methods here, the goal of overriding it within this object is to
   * provide more exact type hinting.
   *
   * @param mixed $offset
   *
   * @return CommandInterface|null
   */
  public function offsetGet($offset): ?CommandInterface
  {
    return parent::offsetGet($offset);
  }
  
  /**
   * offsetSet
   *
   * Adds the value to the collection at the specified index.  Because we can't
   * change the method signature and type hint the $value parameter, we'll add
   * an instanceof check within the method's body to ensure that only
   * CommandInterface objects get added to our collection.
   *
   * @param mixed $offset
   * @param mixed $value
   *
   * @return void
   * @throws CommandCollectionException
   */
  public function offsetSet($offset, $value): void
  {
    if (!($value instanceof CommandInterface)) {
      throw new CommandCollectionException(
        'Cannot add non-Command value to CommandCollection',
        CommandCollectionException::INVALID_VALUE
      );
    }
    
    parent::offsetSet($offset, $value);
  }
}

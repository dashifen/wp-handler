<?php

namespace Dashifen\WPHandler\Agents\Collection;

use Dashifen\Collection\AbstractCollection;
use Dashifen\WPHandler\Agents\AgentInterface;

/**
 * Class AgentCollection
 *
 * @package Dashifen\WPHandler\Agents\Collection
 */
class AgentCollection extends AbstractCollection implements AgentCollectionInterface
{
  /**
   * @var AgentInterface[]
   */
  protected array $collection = [];
  
  /**
   * getCollection
   *
   * Returns the entire collection.
   *
   * @return AgentInterface[]
   */
  public function getCollection(): array
  {
    return parent::getCollection();
  }
  
  /**
   * current
   *
   * Returns the value at the current index in our collection.
   *
   * @return AgentInterface
   */
  public function current(): AgentInterface
  {
    return parent::current();
  }
  
  /**
   * key
   *
   * Returns the current index within the collection.
   *
   * @return string|null
   */
  public function key(): ?string
  {
    return parent::key();
  }
  
  /**
   * valid
   *
   * Returns true if the current index is valid.
   *
   * @return bool
   */
  public function valid(): bool
  {
    return is_string($this->key());
  }
  
  /**
   * offsetGet
   *
   * Returns the value at the specified index within the collection.  Note:
   * we can't alter the method's signature, so we can't type hint $index
   * here.  Instead, we let the phpDocBlock handle that for our IDEs.
   *
   * @param string $index
   *
   * @return AgentInterface|null
   */
  public function offsetGet($index): AgentInterface
  {
    return parent::offsetGet($index);
  }
  
  /**
   * offsetSet
   *
   * Adds the value to the collection at the specified index.  Note:  we
   * can't change the method's signature here, so we can't type hint our
   * parameters.  Instead, we let the phpDocBlock handle it for our IDEs.
   *
   * @param string         $index
   * @param AgentInterface $value
   *
   * @return void
   * @throws AgentCollectionException
   */
  public function offsetSet($index, $value): void
  {
    if (!($value instanceof AgentInterface)) {
      throw new AgentCollectionException(
        'Collection values must implement AgentInterface',
        AgentCollectionException::NOT_AN_AGENT
      );
    }
    
    parent::offsetSet($index, $value);
  }
}

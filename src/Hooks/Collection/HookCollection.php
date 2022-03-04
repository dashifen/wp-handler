<?php

namespace Dashifen\WPHandler\Hooks\Collection;

use Dashifen\WPHandler\Hooks\HookInterface;
use Dashifen\Collection\AbstractCollection;

/**
 * Class HookCollection
 *
 * @package Dashifen\WPHandler\Hooks\Collection
 */
class HookCollection extends AbstractCollection implements HookCollectionInterface
{
  /**
   * @var HookInterface[]
   */
  protected array $collection = [];
  
  /**
   * getCollection
   *
   * Returns the entire collection.  Overridden simply to provide the type
   * hint here in the PHPDoc block.
   *
   * @return HookInterface[]
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
   * @return HookInterface
   */
  public function current(): HookInterface
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
   * because we can't alter the method's signature, we can't type hint $index
   * here.  So, we'll rely on the phpDocBlock to help our IDEs out instead.
   *
   * @param string $index
   *
   * @return HookInterface|null
   */
  public function offsetGet($index): ?HookInterface
  {
    return parent::offsetGet($index);
  }
  
  /**
   * offsetSet
   *
   * Adds the value to the collection at the specified index.  Note:  because
   * we can't alter the method's signature, we can't type hint the parameters
   * here.  So, we'll rely on the phpDocBlock to help our IDEs out instead.
   *
   * @param string        $index
   * @param HookInterface $value
   *
   * @return void
   * @throws HookCollectionException
   */
  public function offsetSet($index, $value)
  {
    if (!($value instanceof HookInterface)) {
      throw new HookCollectionException(
        'Collected objects must implement HookInterface',
        HookCollectionException::NOT_A_HOOK
      );
    }
    
    parent::offsetSet($index, $value);
  }
}

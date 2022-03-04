<?php

namespace Dashifen\WPHandler\Repositories\MenuItems;

use Dashifen\Repository\RepositoryInterface;

interface MenuItemInterface extends RepositoryInterface
{
  /**
   * getParentSlug
   *
   * While we don't usually list getters in interfaces, this one is a little
   * unique because MenuItems don't have a parent slug while SubmenuItems do.
   * Both of those objects implement this interface, so we thought it best
   * to be explicit that both objects have this getter.
   *
   * @return string
   */
  public function getParentSlug(): string;
  
  /**
   * isComplete
   *
   * Returns true if this item is complete and ready to be used within the
   * WordPress ecosystem
   *
   * @return bool
   */
  public function isComplete(): bool;
}

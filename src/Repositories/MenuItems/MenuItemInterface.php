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
     * Since plugin handlers need that information, we'll force menu items to
     * implement this getter to be sure that there's a consistent interface
     * regardless of the existence of an item's parent.
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

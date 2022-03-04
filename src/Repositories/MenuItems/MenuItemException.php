<?php

namespace Dashifen\WPHandler\Repositories\MenuItems;

use Dashifen\Repository\RepositoryException;

class MenuItemException extends RepositoryException
{
  public const ITEM_NOT_READY = 1;
  public const INVALID_CONSTRUCTOR_PARAM = 2;
  public const ATTEMPT_TO_SET_SUBMENU_ICON = 3;
  public const ATTEMPT_TO_SET_SUBMENU_POSITION = 4;
}

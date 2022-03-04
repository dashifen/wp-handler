<?php

namespace Dashifen\WPHandler\Repositories\MenuItems;

use Dashifen\Repository\RepositoryException;
use Dashifen\WPHandler\Handlers\Plugins\PluginHandlerInterface;

/**
 * Class MenuItem
 *
 * @property string $parentSlug
 *
 * @package Dashifen\WPHandler\Repositories\MenuItems
 */
class SubmenuItem extends MenuItem
{
  
  // this changes/overrides the value of the WP_ARGUMENT_ORDER constant from
  // our parent.  these are the argument names and order that submenu items
  // need to return from the toArray() method so that we can, in turn, use
  // that array to call add_submenu_page().
  
  protected const WP_ARGUMENT_ORDER = [
    "parentSlug",
    "pageTitle",
    "menuTitle",
    "capability",
    "menuSlug",
    "callable",
  ];
  
  protected string $parentSlug = "";
  
  /**
   * SubmenuItem constructor.
   *
   * @param PluginHandlerInterface $handler
   * @param array                  $data
   *
   * @throws RepositoryException
   */
  public function __construct(PluginHandlerInterface $handler, array $data = [])
  {
    parent::__construct($handler, $data);
  }
  
  /**
   * getHiddenPropertyNames
   *
   * Ensures that, in addition to anything our parent hides, SubmenuItems
   * also hide the iconUrl and position properties which we inherit but don't
   * need.
   *
   * @return array
   */
  protected function getHiddenPropertyNames(): array
  {
    return array_merge(parent::getHiddenPropertyNames(), ["iconUrl", "position"]);
  }
  
  /**
   * setParentSlug
   *
   * Sets the parent slug property.
   *
   * @param string $parentSlug
   */
  public function setParentSlug(string $parentSlug): void
  {
    $this->parentSlug = $parentSlug;
  }
  
  /**
   * getParentSlug
   *
   * Returns the value of the parent slug property.
   *
   * @return string
   */
  public function getParentSlug(): string
  {
    return $this->parentSlug;
  }
  
  /**
   * setIconUrl
   *
   * Submenu items don't have icons, so this method simply throws an
   * exception.
   *
   * @param string $iconUrl
   *
   * @throws MenuItemException
   */
  public function setIconUrl(string $iconUrl): void
  {
    throw new MenuItemException(
      "Submenu items don't have icons.",
      MenuItemException::ATTEMPT_TO_SET_SUBMENU_ICON
    );
  }
  
  /**
   * setPosition
   *
   * Submenu items don't have positions, so this method simply throws an
   * exception.
   *
   * @param int $position
   *
   * @throws MenuItemException
   */
  public function setPosition(int $position): void
  {
    throw new MenuItemException(
      "Submenu items don't have positions.",
      MenuItemException::ATTEMPT_TO_SET_SUBMENU_POSITION
    );
  }
}

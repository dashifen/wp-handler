<?php

namespace Dashifen\WPHandler\Containers;

use Dashifen\Container\Container;
use Dashifen\WPHandler\Handlers\HandlerInterface;

/**
 * Class MenuItem
 *
 * @package Dashifen\WPHandler\Containers
 * @property string   $pageTitle
 * @property string   $menuTitle
 * @property string   $menuSlug
 * @property string   $capability
 * @property string   $method
 * @property callable $callable
 * @property string   $iconUrl
 * @property int      $position
 */
class MenuItem extends Container implements MenuItemInterface {

  // this item and it's extension, SubmenuItem, need to be converted to an
  // array with a very particular order of indices.  that order is as follows
  // based on the argument order to the WP core add_(sub)menu_item function.

  protected const WP_ARGUMENT_ORDER = ["pageTitle", "menuTitle", "capability",
      "menuSlug", "callable", "iconUrl", "position"];

  /**
   * @var string
   */
  protected $pageTitle = "";

  /**
   * @var string
   */
  protected $menuTitle = "";

  /**
   * @var string
   */
  protected $capability = "";

  /**
   * @var string
   */
  protected $menuSlug = "";

  /**
   * @var string
   */
  protected $method = "";

  /**
   * @var callable
   */
  protected $callable = null;

  /**
   * @var string
   */
  protected $iconUrl = "";

  /**
   * @var int
   */
  protected $position = 26;           // after comments

  /**
   * toArray
   *
   * To absolutely guarantee that we return our properties in the order
   * in which they're declared above, we're going to override the default
   * toArray() method of our parent class and institute this one instead.
   *
   * @return array
   */
  public function toArray (): array {

    // we want to use the WP_ARGUMENT_ORDER constant to be sure that we
    // return our properties in that order.  this is to ensure compatibility
    // with the WP core add_menu_item() and add_submenu_item() functions.

    $properties = [];
    foreach (self::WP_ARGUMENT_ORDER as $property) {
      $properties[$property] = $this->{$property};
    }

    return $properties;
  }

  /**
   * getParentSlug
   *
   * Returns an empty string because only the SubmenuItem class, an extension
   * of this one, has a parent slug.
   *
   * @return string
   */
  public function getParentSlug (): string {
    return "";
  }

  /**
   * isComplete
   *
   * Returns true if this item is complete and ready to be used within the
   * WordPress ecosystem.
   *
   * @return bool
   */
  public function isComplete (): bool {

    // for a menu item to be complete, the properties listed in the
    // WP_ARGUMENT_ORDER constant must not be empty.  we'll iterate over the
    // constant and return false (i.e. incomplete) the second we find an
    // empty one.  if we make it through the list, we're complete.

    foreach (self::WP_ARGUMENT_ORDER as $property) {
      if (empty($this->{$property})) {
        return false;
      }
    }

    return true;
  }

  /**
   * setPageTitle
   *
   * Sets the page title property.  Also sets the menu title and slug
   * properties so that we can use a shortened argument list within our
   * Handlers than what is generally required by add_menu_page().
   *
   * @param string $pageTitle
   *
   * @return void
   */
  public function setPageTitle (string $pageTitle): void {
    $this->pageTitle = $pageTitle;
    $this->setMenuTitle($pageTitle);

    // for our menu slug, we replace all adjacent sets of whitespace
    // non-word characters, and underscores to a dash and lowercase the
    // entire string.  then, we just make sure to remove a dash at the
    // end of the string more for aesthetics than anything else.

    $menuSlug = preg_replace("/[\s|\W|_]+/", "-", strtolower($pageTitle));
    $menuSlug = preg_replace("/-$/", "", $menuSlug);
    $this->setMenuSlug($menuSlug);
  }

  /**
   * setMenuTitle
   *
   * Sets the menu title property
   *
   * @param string $menuTitle
   *
   * @return void
   */
  public function setMenuTitle (string $menuTitle): void {
    $this->menuTitle = $menuTitle;
  }

  /**
   * setMenuSlug
   *
   * Sets the menu slug property.
   *
   * @param string $menuSlug
   *
   * @return void
   */
  public function setMenuSlug (string $menuSlug): void {
    $this->menuSlug = $menuSlug;
  }

  /**
   * setCapability
   *
   * Sets the capability property.
   *
   * @param string $capability
   *
   * @return void
   */
  public function setCapability (string $capability): void {
    $this->capability = $capability;
  }

  /**
   * setMethod
   *
   * Sets the method properties
   *
   * @param string $method
   *
   * @return void
   */
  public function setMethod (string $method): void {
    $this->method = $method;
  }

  /**
   * setCallable
   *
   * Sets the callable property
   *
   * @param callable $callable
   *
   * @return void
   */
  public function setCallable (callable $callable): void {
    $this->callable = $callable;
  }

  /**
   * setMethodAndCallable
   *
   * Sets the method and callable properties.
   *
   * @param HandlerInterface $object
   * @param string           $method
   *
   * @return void
   */
  public function setMethodAndCallable (HandlerInterface $object, string $method): void {
    $this->setCallable([$object, $method]);
    $this->setMethod($method);
  }

  /**
   * setIconUrl
   *
   * Sets the icon URL property.
   *
   * @param string $iconUrl
   *
   * @return void
   */
  public function setIconUrl (string $iconUrl): void {
    $this->iconUrl = $iconUrl;
  }

  /**
   * setPosition
   *
   * Sets the position property which must be a positive number.
   *
   * @param int $position
   *
   * @return void
   */
  public function setPosition (int $position): void {

    // if we don't get a positive number, then we'll stick to our
    // default value of 26 which puts this item after the Comments
    // item in the upper portion of the Dashboard menu.

    $this->position = $position > 0 ? $position : 26;
  }
}
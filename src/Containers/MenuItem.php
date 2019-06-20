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
 * @property string   $parentSlug
 * @property string   $capability
 * @property string   $method
 * @property callable $callable
 * @property string   $iconUrl
 * @property int      $position
 */
class MenuItem extends Container {
  protected const MENU_ITEM_ARGS = ["pageTitle", "menuTitle", "capability", "menuSlug", "method", "iconUrl", "position"];
  protected const SUBMENU_ITEM_ARGS = ["parentSlug", "pageTitle", "menuTitle", "capability", "menuSlug", "method"];


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
  protected $parentSlug = "";

  /**
   * @var string
   */
  protected $capability = "";

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

  public function __construct (array $data = []) {

    // we cheat here because we never expect anyone else to use this
    // object.  here that all you other programmers?  don't use this
    // object.  great; now they're guaranteed to do something untoward
    // with it.  regardless, since this is intended as a helper object
    // for the plugin handler, we know it'll be called with either a
    // seven count array or a six count one.  anything else, we throw
    // a tantrum.

    $dataCount = sizeof($data);
    if ($dataCount !== 6 || $dataCount !== 7) {
      throw new MenuItemException("MenuItems must be constructed with arrays of six or seven indices.");
    }

    // we call this constructor using func_get_args() so that the order
    // prescribed by the plugin handler's addMenuPage and addSubmenuPage
    // methods is preserved.  using that information, we can make an
    // associative array for our parent's constructor as follows.

    $associativeData = $dataCount === 6
      ? array_combine(self::SUBMENU_ITEM_ARGS, $data)
      : array_combine(self::MENU_ITEM_ARGS, $data);

    parent::__construct($associativeData);
  }

  /**
   * toArray
   *
   * Returns an array of a subset of our properties based on whether this
   * represents a menu item or a submenu item within the WP Dashboard.
   *
   * @return array
   */
  public function toArray (): array {

    // the way we can tell the difference between a menu and a submenu
    // item is by checking on the existence of the parent slug property.
    // if we have it, this is a submenu; if we don't, it's a menu.  based
    // on that, we return our information in the order that the WP core
    // add_menu_page() or add_submenu_page() expects it.  luckily, we can
    // use our constants above to define the properties we want to return
    // and then it's just a matter of looping.

    $returnThese = !empty($this->parentSlug)
      ? self::SUBMENU_ITEM_ARGS
      : self::MENU_ITEM_ARGS;

    $returnValue = array_filter(get_object_vars($this), function(string $property) use ($returnThese) {
      return in_array($property, $returnThese);
    }, ARRAY_FILTER_USE_KEY);

    // we know we're going to use the spread operator when we get back to
    // the calling scope.  why?  because we're the programmer of this object,
    // that's why.  remember:  you're not supposed to be using this because
    // it's just an internal structure.  unless you're Dash.  in that case,
    // you're allowed to use it ... for now!

    return array_values($returnValue);
  }

  /**
   * setPageTitle
   *
   * Sets the page title property.
   *
   * @param string $pageTitle
   *
   * @return void
   */
  public function setPageTitle (string $pageTitle): void {
    $this->pageTitle = $pageTitle;
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
   * setParentSlug
   *
   * Sets the parent slug property.
   *
   * @param string $parentSlug
   *
   * @return void
   */
  public function setParentSlug (string $parentSlug): void {
    $this->parentSlug = $parentSlug;
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
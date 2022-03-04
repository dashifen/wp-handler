<?php

namespace Dashifen\WPHandler\Repositories\MenuItems;

use Dashifen\Repository\AbstractRepository;
use Dashifen\Repository\RepositoryException;
use Dashifen\WPHandler\Handlers\Plugins\PluginHandlerInterface;

/**
 * Class MenuItem
 *
 * @property string   $pageTitle
 * @property string   $menuTitle
 * @property string   $menuSlug
 * @property string   $capability
 * @property string   $method
 * @property callable $callable
 * @property string   $iconUrl
 * @property int      $position
 *
 * @package Dashifen\WPHandler\Repositories\MenuItems
 */
class MenuItem extends AbstractRepository implements MenuItemInterface
{
  
  // this item and it's extension, SubmenuItem, need to be converted to an
  // array with a very particular order of indices.  that order is as follows
  // based on the argument order to the WP core add_(sub)menu_item function.
  
  protected const OPTIONAL_ARGUMENTS = ["callable", "iconUrl", "position"];
  protected const WP_ARGUMENT_ORDER = [
    "pageTitle",
    "menuTitle",
    "capability",
    "menuSlug",
    "callable",
    "iconUrl",
    "position",
  ];
  
  protected PluginHandlerInterface $handler;
  protected string $pageTitle = "";
  protected string $menuTitle = "";
  protected string $capability = "";
  protected string $menuSlug = "";
  protected string $method = "";
  protected string $iconUrl = "dashicons-admin-post";
  protected int $position = 26;                // after comments
  
  /**
   * @var callable
   */
  protected $callable = null;
  
  /**
   * MenuItem constructor.
   *
   * @param PluginHandlerInterface $handler
   * @param array                  $data
   *
   * @throws RepositoryException
   */
  public function __construct(PluginHandlerInterface $handler, array $data = [])
  {
    $this->handler = $handler;
    
    // our parent constructor will start to set properties of this object
    // so we need to call it after we set our handler above.  this is
    // because we use the handler in the setCallable() method below.  my
    // usual style is to call the parent constructor first and then do
    // my work for this object, but that won't work in this case.
    
    parent::__construct($data);
  }
  
  /**
   * getHiddenPropertyNames
   *
   * Ensures that the handler property is considered "hidden" within the
   * parent::__get() method.
   *
   * @return array
   */
  protected function getHiddenPropertyNames(): array
  {
    return ["handler"];
  }
  
  /**
   * getCustomPropertyDefaults
   *
   * Intended as a way to provide for functional defaults (e.g. the current
   * date), extensions can override this function to return an array of
   * default values for properties.  that array should be indexed by property
   * names.
   *
   * @return array
   */
  protected function getCustomPropertyDefaults(): array
  {
    return [];
  }
  
  /**
   * getRequiredProperties
   *
   * Returns an array of property names that must be non-empty after
   * construction.
   *
   * @return array
   */
  protected function getRequiredProperties(): array
  {
    return ['pageTitle', 'menuTitle', 'menuSlug', 'capability', 'method'];
  }
  
  
  /**
   * toArray
   *
   * To absolutely guarantee that we return our properties in the order
   * in which they're declared above, we're going to override the default
   * toArray() method of our parent class and institute this one instead.
   *
   * @param string $format
   *
   * @return array
   */
  public function toArray(string $format = ARRAY_N): array
  {
    // we want to use the WP_ARGUMENT_ORDER constant to be sure that we
    // return our properties in that order.  this is to ensure
    // compatibility with the WP core add_menu_item and add_submenu_item
    // functions.
    
    $properties = [];
    foreach (static::WP_ARGUMENT_ORDER as $property) {
      $properties[$property] = $this->{$property};
    }
    
    return $format !== ARRAY_A
      ? array_values($properties)
      : $properties;
  }
  
  /**
   * getParentSlug
   *
   * Returns an empty string because only the SubmenuItem class, an extension
   * of this one, has a parent slug.
   *
   * @return string
   */
  public function getParentSlug(): string
  {
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
  public function isComplete(): bool
  {
    // for a menu item to be complete, the properties listed in the
    // WP_ARGUMENT_ORDER constant must not be empty unless they're also
    // listed in the OPTIONAL_ARGUMENTS constant.  we'll get the items in
    // the former that aren't in the latter, loop over them, and return
    // false if we find an empty one.
    
    $properties = array_diff(static::WP_ARGUMENT_ORDER, static::OPTIONAL_ARGUMENTS);
    
    foreach ($properties as $property) {
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
  public function setPageTitle(string $pageTitle): void
  {
    $this->pageTitle = $pageTitle;
    
    // to homogenize the page title, menu title, and menu slug, if the latter
    // two have not yet been set, we'll set them here.  but, if they're not
    // empty, we assume that the scope using this object knows what it's doing
    // and leave them alone.
    
    if (empty($this->menuTitle)) {
      $this->setMenuTitle($pageTitle);
    }
    
    if (empty($this->menuSlug)) {
  
      // for our menu slug, we replace all adjacent sets of whitespace
      // non-word characters, and underscores to a dash and lowercase the
      // entire string.  then, we just make sure to remove a dash at the end
      // of the string more for aesthetics than anything else.
  
      $menuSlug = preg_replace("/[\s\W_]+/", "-", strtolower($pageTitle));
      $menuSlug = preg_replace("/-$/", "", $menuSlug);
      $this->setMenuSlug($menuSlug);
    }
  }
  
  /**
   * setMenuTitle
   *
   * Sets the menu title property.
   *
   * @param string $menuTitle
   *
   * @return void
   */
  public function setMenuTitle(string $menuTitle): void
  {
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
  public function setMenuSlug(string $menuSlug): void
  {
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
  public function setCapability(string $capability): void
  {
    $this->capability = $capability;
  }
  
  /**
   * setMethod
   *
   * Sets the method and callable properties.
   *
   * @param string $method
   *
   * @return void
   */
  public function setMethod(string $method): void
  {
    $this->setCallable($this->handler, $method);
    $this->method = $method;
  }
  
  /**
   * setCallable
   *
   * Sets the callable property.
   *
   * @param PluginHandlerInterface $object
   * @param string                 $method
   *
   * @return void
   */
  public function setCallable(PluginHandlerInterface $object, string $method): void
  {
    $this->callable = [$object, $method];
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
  public function setIconUrl(string $iconUrl): void
  {
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
  public function setPosition(int $position): void
  {
    // if we don't get a positive number, then we'll stick to our default
    // value of 26 which puts this item after the Comments item in the
    // upper portion of the Dashboard menu.
    
    $this->position = $position > 0 ? $position : 26;
  }
}

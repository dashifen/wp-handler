<?php

namespace Dashifen\WPHandler\Traits;

use Exception;
use ReflectionClassConstant;

trait ActionAndNonceTrait
{
  use CaseChangingTrait;
  
  /**
   * getNonce
   *
   * Returns a nonce for use when verifying the appropriateness of a visitor's
   * submission.
   *
   * @param string|null $action
   *
   * @return string
   */
  protected function getNonce(?string $action = null): string
  {
    return wp_create_nonce($this->getAction($action));
  }
  
  /**
   * getNonceName
   *
   * Given the name of an action, gets a unique name for a nonce based on that
   * action.  Lacking an action, returns _wpnonce, the default name for all WP
   * nonces.
   *
   * @param string|null $action
   *
   * @return string
   */
  protected function getNonceName(?string $action = null): string
  {
    return $action !== null
      ? $this->getAction($action) . '-nonce'
      : '_wpnonce';
  }
  
  /**
   * getAction
   *
   * Returns a string naming the action an on screen form is used to perform.
   * Typically, this is then used to link a form's submission to a method of
   * the object using this trait to process a visitor's work.
   *
   * @param string|null $action
   *
   * @return string
   */
  protected function getAction(?string $action = null): string
  {
    try {
      
      // dash typically adds a class constant named SLUG to their objects
      // which is used for all sorts of things.  one thing we an use it for
      // is a prefix for our actions if it exists.
      
      $slugConstant = new ReflectionClassConstant($this, 'SLUG');
      $prefix = $slugConstant->getValue();
    } catch (Exception $e) {
      
      // if the constant doesn't exist, we'll use a kebab case version of
      // our class name or that class's handler (if it exists).  classes are
      // typically in StudlyCaps so we've used the CaseChangingTrait to convert
      // those to kebab case.
      
      $namespacedClassName = property_exists($this, 'handler')
        ? get_class($this->handler)
        : get_class($this);
      
      $classNameArray = explode('\\', $namespacedClassName);
      $prefix = $this->studlyToKebabCase(array_pop($classNameArray));
    }
    
    return sprintf('%s-%s', $prefix, $action ?? $this->getDefaultAction());
  }
  
  /**
   * getDefaultAction
   *
   * Returns the name of the default action for our getAction method.
   * Typically, this is "save," but users of this Trait can override this as
   * necessary.
   *
   * @return string
   */
  protected function getDefaultAction(): string
  {
    return 'save';
  }
  
  /**
   * isValidActionAndNonce
   *
   * Returns true if the action and nonce contained within our $_REQUEST are
   * valid.  on invalid data, wp_die is called.
   *
   * @param string|null $action
   *
   * @return bool
   */
  protected function isValidActionAndNonce(?string $action = null): bool
  {
    return $this->isValidAction($action, true);
  }
  
  /**
   * isValidAction
   *
   * Uses other methods of this trait to confirm the validity of an action and,
   * when necessary, a nonce contained within the $_REQUEST.  method always
   * returns true because wp_die is called what either are invalid.
   *
   * @param string|null $action
   * @param bool        $checkNonce
   *
   * @return bool
   */
  protected function isValidAction(?string $action = null, bool $checkNonce = false): bool
  {
    $action ??= $this->getDefaultAction();
    $capability = $this->getCapabilityForAction($action);
    if (!current_user_can($capability)) {
      
      // the default title and message can be found in the WP 5.6
      // wp-admin/options.php file.  we add our own filters to make it possible
      // to contextualize either or both of them and then we call wp_die.  the
      // core process also dies, so we'll just follow their lead.
      
      $title = apply_filters('wp-handler-invalid-action-title', 'You need a higher level of permission.');
      $message = apply_filters('wp-handler-invalid-action-message', 'Sorry, you are not allowed to manage options for this site.');
      wp_die('<h1>' . $title . '</h1><p>' . $message . '</p>', 403);
    }
    
    if ($checkNonce) {
      $action = $this->getAction($action);
      $nonce = $this->getNonceName($action);
      if (!wp_verify_nonce($_REQUEST[$nonce] ?? '', $action)) {
        
        // if our action and nonce don't match, then we can call the core
        // wp_nonce_ays (are you sure) function to display an error message.
        // this function the calls wp_die internally, so if we're in here, the
        // execution of this request halts and a response is sent to the
        // visitor.
        
        wp_nonce_ays($action);
      }
    }
    
    // since both failure cases above call wp_die, if we're here, then we can
    // simply return true.  any other situation has already been handled.
    
    return true;
  }
  
  /**
   * getCapabilityForAction
   *
   * Given the name of an action this visitor is attempting to perform,
   * returns the WP capability necessary to do so.  By default, we return
   * manage_options for all actions, but users of this Trait can override this
   * behavior for more specificity.
   *
   * @param string $action
   *
   * @return string
   * @noinspection PhpUnusedParameterInspection
   */
  protected function getCapabilityForAction(string $action): string
  {
    return 'manage_options';
  }
}

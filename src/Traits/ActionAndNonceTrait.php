<?php

namespace Dashifen\WPHandler\Traits;

use Exception;
use ReflectionClassConstant;
use Dashifen\CaseChangingTrait\CaseChangingTrait;

trait ActionAndNonceTrait
{
  use CaseChangingTrait;
  
  // legitimately, this should be true, but some older plugins using this
  // Trait might die a terrible death if we set this flag.  so, we'll play it
  // safe and leave them optional unless the user of this Trait sets it
  // personally.
  
  private bool $requireNonces = false;
  
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
   * action.  Lacking an action, returns one based on the default action
   * returned by getDefaultAction below.
   *
   * @param string|null $action
   *
   * @return string
   */
  protected function getNonceName(?string $action = null): string
  {
    return $this->getAction($action) . '-nonce';
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
      // our class name or that class's handler (if it exists).  then, we see
      // if the class we found has a visible SLUG constant and, if so, we use
      // it.  if not, we'll just use the classname to produce a classname.
      
      $namespacedClassName = property_exists($this, 'handler')
        ? get_class($this->handler)
        : get_class($this);
      
      $prefix = $namespacedClassName::SLUG
        ?? $this->getActionPrefixFromClassName($namespacedClassName);
    }
    
    return sprintf('%s-%s', $prefix, $action ?? $this->getDefaultAction());
  }
  
  /**
   * getActionPrefixFromClassName
   *
   * Extracted from the prior method and made into its own so that it can be
   * overridden by those who use this trait as needed, this method returns a
   * prefix for our actions based on a given namespaced classname.
   *
   * @param string $className
   *
   * @return string
   */
  protected function getActionPrefixFromClassName(string $className): string
  {
    // $className is expected to be a fully namespaced class name.  so, we'll
    // explode it into it's parts, grab the last one, and then, since the PHP
    // styles suggest that class names be in StudlyCaps, we'll conver those to
    // kebab case.
    
    $class = array_reverse(explode('\\', $className))[0];
    return $this->studlyToKebabCase($class);
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
   * userCan
   *
   * The newer, preferred way to check a user's action and, when available,
   * nonce in order to determine that they're authorized to perform a specific
   * action.  It replaces both isValidAction and isValidActionAndNonce.
   *
   * @param string|null $action
   *
   * @return bool
   */
  protected function userCan(?string $action = null): bool
  {
    $action ??= $this->getDefaultAction();
    if (!current_user_can($this->getCapabilityForAction($action))) {
      
      // the default title and message can be found in the core
      // wp-admin/options.php file.  we add our own filters to make it possible
      // to contextualize either or both of them and then we call wp_die.  the
      // core process also dies, so we'll just follow their lead.
      
      $title = apply_filters('wp-handler-invalid-action-title', 'You need a higher level of permission.');
      $message = apply_filters('wp-handler-invalid-action-message', 'Sorry, you are not allowed to manage options for this site.');
      wp_die('<h1>' . $title . '</h1><p>' . $message . '</p>', 403);
    }
    
    // now, if there is a nonce in the request that has name that matches our
    // action, we want to test it.  if a nonce isn't found, we check the value
    // of our requireNonces flag; if it's set then the lack of a nonce is an
    // error.  if we found a nonce, we verify it.  if either of these criteria
    // are not met, we'll call the core function that handles nonce problems.
    
    $nonce = $this->getNonceName($action);
    $nonceFound = isset($_REQUEST[$nonce]);
    $nonceNeeded = !$nonceFound && $this->requireNonces;
    $nonceInvalid = $nonceFound && !wp_verify_nonce($_REQUEST[$nonce],
        $this->getAction($action));
    
    if ($nonceNeeded || $nonceInvalid) {
      
      // if our action and nonce don't match, then we can call the core
      // wp_nonce_ays (are you sure) function to display an error message.
      // this function the calls wp_die internally, so if we're in here, the
      // execution of this request halts and a response is sent to the
      // visitor.
      
      wp_nonce_ays($action);
    }
    
    // since both failure cases above call wp_die, if we're here, then we can
    // simply return true.  any other situation has already been handled.
    
    return true;
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
    trigger_error(
      'The isValidActionAndNonce method is deprecated; use userCan instead.',
      E_USER_DEPRECATED
    );
    
    return $this->userCan($action);
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
   * @noinspection PhpUnusedParameterInspection
   */
  protected function isValidAction(?string $action = null, bool $checkNonce = false): bool
  {
    trigger_error(
      'The isValidAction method is deprecated; use userCan instead.',
      E_USER_DEPRECATED
    );
    
    return $this->userCan($action);
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

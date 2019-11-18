<?php

namespace Dashifen\WPHandler\Traits;

use Dashifen\WPHandler\Handlers\HandlerException;
use Dashifen\Transformer\StorageTransformer\StorageTransformerInterface;

/**
 * Trait OptionsManagementTrait
 *
 * Provides methods for the getting and updating of Handler options.
 *
 * @property StorageTransformerInterface $transformer
 *
 * @package Dashifen\WPHandler\Traits
 */
trait OptionsManagementTrait {
  /**
   * getOption
   *
   * Does a little extra work before calling the WP Core get_option()
   * function to retrieve information from the database.  If this handler
   * has a transformer, we'll use it to transform the retrieved value.
   *
   * @param string $option
   * @param mixed  $default
   * @param bool   $transform
   *
   * @return mixed
   * @throws HandlerException
   */
  public function getOption (string $option, $default = '', bool $transform = true) {

    // it's hard to make a trait know about the methods that are available in
    // the classes in which it might be used.  so, we won't use the isDebug()
    // method here, we'll just execute the same command that it does.

    if ($this->isOptionValid($option, defined('WP_DEBUG') && WP_DEBUG)) {
      $fullOptionName = $this->getOptionNamePrefix() . $option;
      $value = get_option($fullOptionName, $default);

      return $transform && $this->hasTransformer()
        ? $this->transformer->transformFromStorage($option, $value)
        : $value;
    }

    // finally, if the option was not valid, we just return the default
    // value and hope for the best.

    return $default;
  }

  /**
   * isOptionValid
   *
   * Returns true if the option we're working with is valid with respect to
   * this handler's sphere of influence.  if it's not, it'll either return
   * false or throw a HandlerException based on the value of $throw.
   *
   * @param string $option
   * @param bool   $throw
   *
   * @return bool
   * @throws HandlerException
   */
  protected function isOptionValid (string $option, bool $throw = true): bool {
    $isValid = in_array($option, $this->getOptionNames());

    if (!$isValid && $throw) {
      throw new HandlerException('Unknown option:' . $option,
        HandlerException::UNKNOWN_OPTION);
    }

    return $isValid;
  }

  /**
   * getOptionNames
   *
   * Returns an array of valid option names for use within the isOptionValid
   * method.
   *
   * @return array
   */
  abstract protected function getOptionNames (): array;

  /**
   * getSettingsPrefix
   *
   * Returns the prefix that that is used to differentiate the options for
   * this handler's sphere of influence from others.  By default, we return an
   * empty string, but we assume that this will likely get overridden.
   *
   * @return string
   */
  public function getOptionNamePrefix (): string {
    return '';
  }

  /**
   * hasTransformer
   *
   * Returns true if this object has a transformer property that implements the
   * StorageTransformerInterface interface.
   *
   * @return bool
   */
  protected function hasTransformer (): bool {
    return property_exists($this, "transformer")
      && $this->transformer instanceof StorageTransformerInterface;
  }

  /**
   * getAllOptions
   *
   * Loops over the array of option names and returns their values as an array
   * transforming them as necessary.
   *
   * @param bool $transform
   *
   * @return array
   * @throws HandlerException
   */
  public function getAllOptions (bool $transform = true): array {
    foreach ($this->getOptionNames() as $optionName) {
      $options[$optionName] = $this->getOption($optionName, '', $transform);
    }

    // just in case someone calls this function on a handler that doesn't have
    // any options to retrieve, we'll need to use the null coalescing operator
    // to ensure that we return an empty array in the event that $options is
    // not defined in the above loop.

    return $options ?? [];
  }

  /**
   * getAllOptionsInOne
   *
   * Sometimes is important to be sure we use the minimum number of database
   * queries.  This will pull an array from the database in a single query and
   * then transform it and return that array.  It'll only have data to provide
   * if updateAllOptionsInOne has been used to store these options in the
   * database in this capacity.
   *
   * @param bool $transform
   *
   * @return array
   * @throws HandlerException
   */
  public function getAllOptionsInOne (bool $transform = true): array {
    $optionsInOneName = $this->getOptionsInOneName();
    $optionsInOne = get_option($optionsInOneName, []);

    if ($transform && $this->hasTransformer()) {

      // as long as we want to transform and have a transformer, we'll go for
      // it.  notice that the $value variable within our loop is a reference.
      // thus, when we're done, we will have actually transformed the array
      // we return below.

      foreach ($optionsInOne as $option => &$value) {
        $value = $this->transformer->transformFromStorage($option, $value);
      }
    }

    return $optionsInOne;
  }

  /**
   * getOptionsInOneName
   *
   * Returns a unique name for this handler's settings for use when saving or
   * retrieving them in a single database call.
   *
   * @return string
   * @throws HandlerException
   */
  protected function getOptionsInOneName (): string {

    // to try and make a automatic and repeatably generated option name, we'll
    // create the sha1 hash of our option names and add our prefix so that a
    // human will be able to see and recognize the hash as being linked to the
    // rest of this handler's data.  a programmer can always override this as
    // necessary.  note:  we check the length of the option name because the
    // codex entry indicates that option names should not exceed 64 characters
    // in length.

    $optionNames = $this->getOptionNames();
    $hashedNames = sha1(join('', $optionNames));
    $optionsInOneName = $this->getOptionNamePrefix() . $hashedNames;

    if (strlen($optionsInOneName) > 64) {
      throw new HandlerException("Option name too long:  $optionsInOneName",
        HandlerException::OPTION_TOO_LONG);
    }

    return $optionsInOneName;
  }

  /**
   * updateOption
   *
   * Ensures that we save this option's value using this plugin's option
   * prefix before calling WordPress's update_option() function and returning
   * its results.
   *
   * @param string $option
   * @param string $value
   * @param bool   $transform
   *
   * @return bool
   * @throws HandlerException
   */
  public function updateOption (string $option, string $value, bool $transform = true): bool {
    if ($this->isOptionValid($option)) {

      $value = $transform && $this->hasTransformer()
        ? $this->transformer->transformForStorage($option, $value)
        : $value;

      $fullOptionName = $this->getOptionNamePrefix() . $option;
      return update_option($fullOptionName, $value);
    }

    return false;
  }

  /**
   * updateAllOptions
   *
   * Like the getAllOptions method above, this saves all of our information in
   * one call based on the mapping of option names to values represented by the
   * first parameter.
   *
   * @param array $values
   * @param bool  $transform
   *
   * @return bool
   * @throws HandlerException
   */
  public function updateAllOptions (array $values, bool $transform = true): bool {
    $success = true;

    foreach ($values as $option => $value) {

      // the updateOption method returns true when it updates our option.
      // we Boolean AND that value with the current value of $success which
      // starts as true.  so, as long as updateOption return true, $success
      // will remain set.  but, the first time we hit a problem, it'll be
      // reset and will remain so because false AND anything is false.

      $success = $success && $this->updateOption($option, $value, $transform);
    }

    return $success;
  }

  /**
   * updateAllOptionsInOne
   *
   * To reduce the number of database calls, this method saves all of this
   * handlers options in a single database entry.
   *
   * @param array $values
   * @param bool  $transform
   *
   * @return bool
   * @throws HandlerException
   */
  public function updateAllOptionsInOne(array $values, bool $transform = true): bool {
    if ($transform && $this->hasTransformer()) {

      // if we want to transform and have a transformer, we'll go for it.  note
      // that $value is a reference, so the changes we make within the loop
      // will remain when it completes.

      foreach ($values as $option => &$value) {
        $value = $this->transformer->transformForStorage($option, $value);
      }
    }

    return update_option($this->getOptionsInOneName(), $values);
  }

  /**
   * optionValueMatches
   *
   * Returns true if the $option's value in the database matches $value.  This
   * is useful when determining whether or not an update to this option is
   * necessary.
   *
   * @param string $option
   * @param mixed  $value
   * @param bool   $transform
   *
   * @return bool
   * @throws HandlerException
   */
  public function optionValueMatches (string $option, $value, bool $transform = true): bool {

    // we don't want our handler to transform the value of $field as it comes
    // out of the database.  doing so would likely mean that it would become
    // different from $value causing the system to try and update things even
    // if it doesn't have to.  hence, we pass a false-flag to the getOption
    // method which prevents it from performing its transformations.

    return $this->getOption($option, '', $transform) === $value;
  }
}
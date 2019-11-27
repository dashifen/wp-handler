<?php

namespace Dashifen\WPHandler\Traits;

use Dashifen\WPHandler\Handlers\HandlerException;
use Dashifen\Transformer\StorageTransformer\StorageTransformerInterface;

/**
 * Trait OptionsManagementTrait
 *
 * Provides methods for the getting and updating of Handler's options as well
 * as a mechanism for storing option values in memory rather than frequently
 * selecting them from the database.
 *
 * @property StorageTransformerInterface $transformer
 *
 * @package Dashifen\WPHandler\Traits
 */
trait OptionsManagementTrait {
  /**
   * @var array
   */
  protected $optionsCache = [];

  /**
   * @var bool
   */
  protected $useOptionsCache = false;

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
    if ($this->isOptionCached($option)) {
      return $this->getCachedOption($option);
    }

    $value = $default;

    // it's hard to make a trait know about the methods that are available in
    // the classes in which it might be used.  so, we won't use the isDebug()
    // method here, we'll just execute the same command that it does.

    if ($this->isOptionValid($option, defined('WP_DEBUG') && WP_DEBUG)) {
      $fullOptionName = $this->getOptionNamePrefix() . $option;
      $value = get_option($fullOptionName, $default);

      $value = $transform && $this->hasTransformer()
        ? $this->transformer->transformFromStorage($option, $value)
        : $value;
    }

    // here, we either selected a value from the database or it was set to the
    // default before the if-block above.  regardless, if we're using the cache
    // we want to remember it for next time.

    $this->maybeCacheOption($option, $value);
    return $value;
  }

  /**
   * isOptionCached
   *
   * Given the name of an option, determines if a value for it exists in
   * the cache.
   *
   * @param string $option
   *
   * @return bool
   */
  protected function isOptionCached (string $option) {
    return $this->useOptionsCache && isset($this->optionsCache[$option]);
  }

  /**
   * getCachedOption
   *
   * Given the name of the option, returns the value for it in the cache.
   * Assumes that isOptionCached() has been previously called but uses the null
   * coalescing operator to return null if a mistake was made.
   *
   * @param string $option
   *
   * @return mixed
   */
  protected function getCachedOption (string $option) {
    return $this->optionsCache[$option] ?? null;
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
   * maybeCacheOption
   *
   * If we're using the cache, we add this option/value pair to it.
   *
   * @param string $option
   * @param mixed  $value
   *
   * @return void
   */
  protected function maybeCacheOption (string $option, $value): void {
    if ($this->useOptionsCache) {
      $this->optionsCache[$option] = $value;
    }
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

      // we don't have to worry about accessing the cache here because, if
      // we're using it, the getOption method will use it internally.

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
   * @param string $optionsInOneName
   * @param bool   $transform
   *
   * @return array
   * @throws HandlerException
   */
  public function getAllOptionsInOne (string $optionsInOneName = '', bool $transform = true): array {
    if (empty($optionsInOneName)) {
      $optionsInOneName = $this->getOptionsInOneName();
    }

    // just like singular options that we might select above, we might have an
    // in-memory cache of our complete option set.  if so, we'll want to use
    // it to cut down on database queries.

    if ($this->isOptionCached($optionsInOneName)) {
      return $this->getCachedOption($optionsInOneName);
    }

    // if we didn't have a cached version of our options, we'll select them
    // from the database.  then, we loop ovr them and transform each value if
    // necessary.

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

    $this->maybeCacheOptions($optionsInOne);
    return $optionsInOne;
  }

  /**
   * maybeCacheOptions
   *
   * If we're using our options cache, then this method stores what we selected
   * from the database in memory so that we don't have to select and re-select
   * it over and over again.
   *
   * @param array $options
   */
  protected function maybeCacheOptions (array $options): void {
    if ($this->useOptionsCache) {

      // if we're here, then we're using our options cache.  so, we'll merge the
      // options in our parameter into the cache so that we have a record of what
      // we selected.  we don't replace the cache with $options because that
      // might destroy other data that we didn't select this time.

      $this->optionsCache = array_merge($this->optionsCache, $options);
    }
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
   * @param mixed  $value
   * @param bool   $transform
   *
   * @return bool
   * @throws HandlerException
   */
  public function updateOption (string $option, $value, bool $transform = true): bool {

    // since we transform our $value before we cram it in the database, it's
    // easier for us to (maybe) add it to our cache first.  that way, we have
    // the value the visitor sent us in memory and we don't have to remember to
    // transform it before using it elsewhere.

    $this->maybeCacheOption($option, $value);

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

      // the updateOption method returns true when it updates our option.  we
      // Boolean AND that value with the current value of $success which starts
      // as true.  so, as long as updateOption return true, $success will
      // remain set.  but, the first time we hit a problem, it'll be reset and
      // will remain so because false AND anything is false.

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
   * @param array  $values
   * @param string $optionsInOneName
   * @param bool   $transform
   *
   * @return bool
   * @throws HandlerException
   */
  public function updateAllOptionsInOne (array $values, string $optionsInOneName = '', bool $transform = true): bool {
    if (empty ($optionsInOneName)) {
      $optionsInOneName = $this->getOptionsInOneName();
    }

    // since we're about to transform our values for storage, it's easier for
    // us to maybe store them in the cache first, then transform, then update
    // the database.  then, we also update the record of all of our options
    // in the cache as well.

    $this->maybeCacheOptions($values);
    $this->maybeCacheOption($optionsInOneName, $values);
    if ($transform && $this->hasTransformer()) {

      // if we want to transform and have a transformer, we'll go for it.  note
      // that $value is a reference, so the changes we make within the loop
      // will remain when it completes.

      foreach ($values as $option => &$value) {
        $value = $this->transformer->transformForStorage($option, $value);
      }
    }

    return update_option($optionsInOneName, $values);
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
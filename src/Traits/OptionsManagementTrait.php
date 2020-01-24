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
trait OptionsManagementTrait
{
    /**
     * @var array
     */
    private $optionsCache = [];
    
    /**
     * @var bool
     */
    private $useOptionsCache = false;
    
    /**
     * @var string
     */
    private $optionSnapshotName = null;
    
    /**
     * getOption
     *
     * Does a little extra work before retrieving our option value from the
     * database.  If this handler has a transformer, we'll use it to transform
     * the retrieved value.
     *
     * @param string $option
     * @param mixed  $default
     * @param bool   $transform
     *
     * @return mixed
     * @throws HandlerException
     */
    public function getOption(string $option, $default = '', bool $transform = true)
    {
        if ($this->isOptionCached($option)) {
            return $this->getCachedOption($option);
        }
        
        // it's hard to make a trait know about the methods that are available
        // in the classes in which it might be used.  so, we won't use the
        // isDebug method here, we'll just execute the same command that it
        // does.
        
        if ($this->isOptionValid($option, defined('WP_DEBUG') && WP_DEBUG)) {
            $fullOptionName = $this->getOptionNamePrefix() . $option;
            $value = $this->retrieveOption($fullOptionName, $default);
            $value = $transform && $this->hasOptionTransformer()
              ? $this->transformer->transformFromStorage($option, $value)
              : $value;
        }
        
        // here, if we didn't set $value in our if-block, we'll do so here with
        // the null coalescing operator.  then, if we're using the cache we
        // want to remember it for next time.
    
        $value = $value ?? $default;
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
    protected function isOptionCached(string $option)
    {
        return $this->useOptionsCache && isset($this->optionsCache[$option]);
    }
    
    /**
     * getCachedOption
     *
     * Given the name of the option, returns the value for it in the cache.
     * Assumes that isOptionCached() has been previously called but uses the
     * null coalescing operator to return null if a mistake was made.
     *
     * @param string $option
     *
     * @return mixed
     */
    protected function getCachedOption(string $option)
    {
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
    protected function isOptionValid(string $option, bool $throw = true): bool
    {
        $isValid = in_array($option, $this->getValidOptionNames());
        
        if (!$isValid && $throw) {
            throw new HandlerException(
              'Unknown option:' . $option,
              HandlerException::UNKNOWN_OPTION
            );
        }
        
        return $isValid;
    }
    
    /**
     * getValidOptionNames
     *
     * The full set of options names include the custom options managed by the
     * handler or agent using this trait and the name of the options snapshot
     * identified herein.  This method just makes sure to add the latter to the
     * former.
     *
     * @return array
     */
    protected function getValidOptionNames (): array
    {
        $options = $this->getOptionNames();
        $options[] = $this->getOptionSnapshotName();
        return $options;
    }
    
    /**
     * getOptionNames
     *
     * Returns an array of valid option names for use within the isOptionValid
     * method.
     *
     * @return array
     */
    abstract protected function getOptionNames(): array;
    
    /**
     * getOptionNamePrefix
     *
     * Returns the prefix that that is used to differentiate the options for
     * this handler's sphere of influence from others.  By default, we return
     * an empty string, but we assume that this will likely get overridden.
     * Public in case an agent needs to ask their handler what prefix to use.
     *
     * @return string
     */
    public function getOptionNamePrefix(): string
    {
        return '';
    }
    
    /**
     * retrieveOption
     *
     * Retrieves an option from the database.  Separated from its surrounding
     * scope so we can override this, e.g. for network options.
     *
     * @param string $option
     * @param mixed  $default
     *
     * @return mixed
     */
    protected function retrieveOption (string $option, $default = '')
    {
        return get_option($option, $default);
    }
    
    /**
     * hasOptionTransformer
     *
     * Returns true if this object has a transformer property that implements
     * the StorageTransformerInterface interface.  Name is oddly specific so
     * that it doesn't conflict with method of similar purpose in the
     * PostMetaManagementTrait should both this one and that one be used by the
     * same object.
     *
     * @return bool
     */
    protected function hasOptionTransformer(): bool
    {
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
    protected function maybeCacheOption(string $option, $value): void
    {
        if ($this->useOptionsCache) {
            $this->optionsCache[$option] = $value;
        }
    }
    
    /**
     * getAllOptions
     *
     * Loops over the array of option names and returns their values as an
     * array transforming them as necessary.
     *
     * @param bool $transform
     *
     * @return array
     * @throws HandlerException
     */
    public function getAllOptions(bool $transform = true): array
    {
        foreach ($this->getOptionNames() as $optionName) {
            // we don't have to worry about accessing the cache here because,
            // if we're using it, the getOption method will use it internally.
            
            $options[$optionName] = $this->getOption($optionName, '', $transform);
        }
        
        // just in case someone calls this function on a handler that doesn't
        // have any options to retrieve, we'll need to use the null coalescing
        // operator to ensure that we return an empty array in the event that
        // $options is not defined in the above loop.
        
        return $options ?? [];
    }
    
    /**
     * getOptionsSnapshot
     *
     * Sometimes is important to be sure we use the minimum number of database
     * queries.  This will pull an array from the database in a single query
     * and then transform it and return that array.  It'll only have data to
     * provide if updateOptionsSnapshot has been used to store these options
     * in the database in this capacity.
     *
     * @param bool   $transform
     *
     * @return array
     */
    public function getOptionsSnapshot(bool $transform = true): array
    {
        // just like singular options that we might select above, we might have
        // an in-memory cache of our complete option set.  if so, we'll want to
        // use it to cut down on database queries.
    
        $snapshotName = $this->getOptionSnapshotName();
        if ($this->isOptionCached($snapshotName)) {
            return $this->getCachedOption($snapshotName);
        }
        
        // if we didn't have a cached version of our options, we'll select them
        // from the database.  then, we loop ovr them and transform each value
        // if necessary.  because we might loop after our selection, we default
        // to an empty array if we've not previously saved a snapshot for these
        // options.
        
        $snapshot = $this->retrieveOption($snapshotName, []);
        if ($transform && $this->hasOptionTransformer()) {
            // as long as we want to transform and have a transformer, we'll go
            // for it.  notice that the $value variable within our loop is a
            // reference. thus, when we're done, we will have actually
            // transformed the array we return below.
            
            foreach ($snapshot as $option => &$value) {
                $value = $this->transformer->transformFromStorage($option, $value);
            }
        }
        
        $this->maybeCacheOption($snapshotName, $snapshot);
        return $snapshot;
    }
    
    /**
     * getSnapshotName
     *
     * Returns a unique name for this handler's settings for use when saving or
     * retrieving them in a single database call.
     *
     * @return string
     */
    protected function getOptionSnapshotName(): string
    {
        if ($this->optionSnapshotName !== null) {
            
            // if we've already done the work below, we don't need to do it
            // again.  sure, we're only saving fractions of seconds but maybe
            // every little bit counts, and for a big array of options, the
            // join and hashing operation below could be expensive.
            
            return $this->optionSnapshotName;
        }
    
        // to try and make a automatic and repeatably generated option name,
        // we'll create the sha1 hash of our option names and add our prefix so
        // that a human will be able to see and recognize the hash as being
        // linked to the rest of this handler's data.  a programmer can always
        // override this as necessary.
    
        $hashedNames = sha1(join('', $this->getOptionNames()));
        $snapshotName = $this->getOptionNamePrefix() . $hashedNames;
        
        // the codex tells us that an option name should be no longer than 64
        // characters.  which is weird since the column is a VARCHAR(191)
        // field.  but, we'll follow the rules and make sure that out option
        // name is no longer than the codex-specified limit.
        
        return ($this->optionSnapshotName = substr($snapshotName, 0, 64));
    }
    
    /**
     * updateOption
     *
     * Ensures that we save this option's value using this plugin's option
     * prefix before calling the storeOption method and returning its results.
     *
     * @param string $option
     * @param mixed  $value
     * @param bool   $transform
     *
     * @return bool
     * @throws HandlerException
     */
    public function updateOption(string $option, $value, bool $transform = true): bool
    {
        // since we transform our $value before we cram it in the database,
        // it's easier for us to (maybe) add it to our cache first.  that way,
        // we have the value the visitor sent us in memory and we don't have to
        // remember to transform it before using it elsewhere.
        
        $this->maybeCacheOption($option, $value);
        
        if ($this->isOptionValid($option)) {
            $value = $transform && $this->hasOptionTransformer()
              ? $this->transformer->transformForStorage($option, $value)
              : $value;
            
            $fullOptionName = $this->getOptionNamePrefix() . $option;
            return $this->storeOption($fullOptionName, $value);
        }
        
        return false;
    }
    
    /**
     * storeOption
     *
     * Stores a value in the database.  Separated from other scopes so this
     * behavior can be overridden, e.g. for the storage of network options.
     *
     * @param string $option
     * @param mixed  $value
     *
     * @return bool
     */
    protected function storeOption (string $option, $value): bool
    {
        return update_option($option, $value);
    }
    
    /**
     * updateAllOptions
     *
     * Like the getAllOptions method above, this saves all of our information
     * in one call based on the mapping of option names to values represented
     * by the first parameter.
     *
     * @param array $values
     * @param bool  $transform
     *
     * @return bool
     * @throws HandlerException
     */
    public function updateAllOptions(array $values, bool $transform = true): bool
    {
        $success = true;
        foreach ($values as $option => $value) {
            
            // the updateOption method returns true when it updates our option.
            // we Boolean AND that value with the current value of $success
            // which starts as true.  so, as long as updateOption return true,
            // $success will remain set.  but, the first time we hit a problem,
            // it'll be reset and will remain so because false AND anything is
            // false.
            
            $success = $success && $this->updateOption($option, $value, $transform);
        }
        
        return $success;
    }
    
    /**
     * updateOptionsSnapshot
     *
     * To reduce the number of database calls, this method saves all of this
     * handlers options in a single database entry.
     *
     * @param array  $values
     * @param bool   $transform
     *
     * @return bool
     * @throws HandlerException
     */
    public function updateOptionsSnapshot(array $values, bool $transform = true): bool
    {
        // since we're about to transform our values for storage, it's easier
        // for us to maybe store them in the cache first, then transform, then
        // update the database.  then, we also update the record of all of our
        // options in the cache as well.  finally, we update this information
        // in the individual options as well so that the snapshot records
        // matches.
        
        $snapshotName = $this->getOptionSnapshotName();
        $this->maybeCacheOption($snapshotName, $values);
        $this->updateAllOptions($values, $transform);
        
        if ($transform && $this->hasOptionTransformer()) {
            
            // if we want to transform and have a transformer, we'll go for it.
            // note that $value is a reference, so the changes we make within
            // the loop will remain when it completes.
            
            foreach ($values as $option => &$value) {
                $value = $this->transformer->transformForStorage($option, $value);
            }
        }
        
        return $this->storeOption($snapshotName, $values);
    }
    
    /**
     * optionValueMatches
     *
     * Returns true if the $option's value in the database matches $value.
     * This is useful when determining whether or not an update to this option
     * is necessary.
     *
     * @param string $option
     * @param mixed  $value
     * @param bool   $transform
     *
     * @return bool
     * @throws HandlerException
     */
    public function optionValueMatches(string $option, $value, bool $transform = true): bool
    {
        // we don't want our handler to transform the value of $field as it
        // comes out of the database.  doing so would likely mean that it would
        // become different from $value causing the system to try and update
        // things even if it doesn't have to.  hence, we pass a false-flag to
        // the getOption method which prevents it from performing its
        // transformations.
        
        return $this->getOption($option, '', $transform) === $value;
    }
}

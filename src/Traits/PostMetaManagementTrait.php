<?php

namespace Dashifen\WPHandler\Traits;

use Dashifen\WPHandler\Handlers\HandlerException;
use Dashifen\Transformer\StorageTransformer\StorageTransformerInterface;

/**
 * Trait PostMetaManagementTrait
 *
 * Provides methods for the getting and updating of a post's meta as well
 * as a mechanism for storing post meta values in memory rather than frequently
 * selecting them from the database.  This is intentionally similar in syntax
 * and semantics to the OptionsManagementTrait.
 *
 * @property StorageTransformerInterface $transformer
 *
 * @package Dashifen\WPHandler\Traits
 */
trait PostMetaManagementTrait
{
    /**
     * @var array
     */
    private $postMetaCache = [];
    
    /**
     * @var bool
     */
    private $usePostMetaCache = false;
    
    /**
     * @var string
     */
    private $postMetaSnapshotName = null;
    
    /**
     * getPostMeta
     *
     * Does a little extra work before retrieving our post meta value from the
     * database.  If this handler has a transformer, we'll use it to transform
     * the retrieved value.
     *
     * @param int    $postId
     * @param string $postMeta
     * @param mixed  $default
     * @param bool   $single
     * @param bool   $transform
     *
     * @return mixed
     * @throws HandlerException
     */
    public function getPostMeta (int $postId, string $postMeta, $default = '', bool $single = true, bool $transform = true)
    {
        if ($this->isPostMetaCached($postId, $postMeta)) {
            return $this->getCachedPostMeta($postId, $postMeta);
        }
        
        // it's hard to make a trait know about the methods that are available
        // in the classes in which it might be used.  so, we won't use the
        // isDebug method here, we'll just execute the same command that it
        // does with respect to the WP_DEBUG constant.
        
        if ($this->isPostMetaValid($postMeta, defined('WP_DEBUG') && WP_DEBUG)) {
            $fullPostMetaName = $this->getPostMetaNamePrefix() . $postMeta;
            $value = $this->retrievePostMeta($postId, $fullPostMetaName, $default, $single);
            $value = $transform && $this->hasTransformer()
                ? $this->transformer->transformFromStorage($postMeta, $value)
                : $value;
        }
        
        // here, if we didn't set $value in our if-block, we'll do so here with
        // the null coalescing operator.  then, if we're using the cache we
        // want to remember it for next time.
    
        $value = $value ?? $default;
        $this->maybeCachePostMeta($postId, $postMeta, $value);
        return $value;
    }
    
    /**
     * isPostMetaCached
     *
     * Given the name of an postMeta, determines if a value for it exists in
     * the cache.
     *
     * @param int    $postId
     * @param string $postMeta
     *
     * @return bool
     */
    protected function isPostMetaCached (int $postId, string $postMeta)
    {
        return $this->usePostMetaCache
            && isset($this->postMetaCache[$postId][$postMeta]);
    }
    
    /**
     * getCachedPostMeta
     *
     * Given the name of the post meta, returns the value for it in the cache.
     * Assumes that isPostMetaCached() has been previously called but uses the
     * null coalescing operator to return null if a mistake was made.
     *
     * @param int    $postId
     * @param string $postMeta
     *
     * @return mixed
     */
    protected function getCachedPostMeta (int $postId, string $postMeta)
    {
        return $this->postMetaCache[$postId][$postMeta] ?? null;
    }
    
    /**
     * isPostMetaValid
     *
     * Returns true if the post meta we're working with is valid with respect
     * to this object's sphere of influence.  if it's not, it'll either return
     * false or throw a HandlerException based on the value of $throw.
     *
     * @param string $postMeta
     * @param bool   $throw
     *
     * @return bool
     * @throws HandlerException
     */
    protected function isPostMetaValid (string $postMeta, bool $throw = true): bool
    {
        $isValid = in_array($postMeta, $this->getValidPostMetaNames());
        
        if (!$isValid && $throw) {
            throw new HandlerException(
                'Unknown postMeta:' . $postMeta,
                HandlerException::UNKNOWN_OPTION
            );
        }
        
        return $isValid;
    }
    
    /**
     * getValidPostMetaNames
     *
     * The full set of post meta names, including the custom post meta managed
     * by the handler or agent using this trait and the name of the post meta
     * snapshot, are identified herein.  This method just makes sure to add the
     * latter to the former.
     *
     * @return array
     */
    protected function getValidPostMetaNames (): array
    {
        $postMeta = $this->getPostMetaNames();
        $postMeta[] = $this->getPostMetaSnapshotName();
        return $postMeta;
    }
    
    /**
     * getPostMetaNames
     *
     * Returns an array of valid post meta names for use within the
     * isPostMetaValid method.
     *
     * @return array
     */
    abstract protected function getPostMetaNames (): array;
    
    /**
     * getSettingsPrefix
     *
     * Returns the prefix that that is used to differentiate the post meta for
     * this handler's sphere of influence from others.  By default, we return
     * an empty string, but we assume that this will likely get overridden.
     * Public in case an agent needs to ask their handler what prefix to use.
     *
     * @return string
     */
    public function getPostMetaNamePrefix (): string
    {
        return '';
    }
    
    /**
     * retrievePostMeta
     *
     * Retrieves a post meta value from the database.  Separated from its
     * surrounding scope so we can override this, e.g. for term meta.
     *
     * @param int    $postId
     * @param string $postMeta
     * @param mixed  $default
     * @param bool   $single
     *
     * @return mixed
     */
    protected function retrievePostMeta (int $postId, string $postMeta, $default = '', bool $single = true)
    {
        $meta = get_post_meta($postId, $postMeta, $single);
        
        // single meta fields return an empty string when they don't exist;
        // non-single fields return an empty array.  so, based on the state of
        // $single, we compare $meta to these defaults and return $default when
        // it makes sense to do so.  this, effectively, adds default value
        // behaviors to post meta which don't exist in WP core.
        
        return ($single && $meta === '') || (!$single && $meta === [])
            ? $default
            : $meta;
    }
    
    /**
     * hasTransformer
     *
     * Returns true if this object has a transformer property that implements
     * the StorageTransformerInterface interface.
     *
     * @return bool
     */
    protected function hasTransformer (): bool
    {
        return property_exists($this, "transformer")
            && $this->transformer instanceof StorageTransformerInterface;
    }
    
    /**
     * maybeCachePostMeta
     *
     * If we're using the cache, we add this postMeta/value pair to it.
     *
     * @param int    $postId
     * @param string $postMeta
     * @param mixed  $value
     *
     * @return void
     */
    protected function maybeCachePostMeta (int $postId, string $postMeta, $value): void
    {
        if ($this->usePostMetaCache) {
            $this->postMetaCache[$postId][$postMeta] = $value;
        }
    }
    
    /**
     * getAllPostMeta
     *
     * Loops over the array of post meta names and returns their values as an
     * array transforming them as necessary.
     *
     * @param int  $postId
     * @param bool $single
     * @param bool $transform
     *
     * @return array
     * @throws HandlerException
     */
    public function getAllPostMeta (int $postId, bool $single = true, bool $transform = true): array
    {
        foreach ($this->getPostMetaNames() as $postMetaName) {
            
            // we don't have to worry about accessing the cache here because,
            // if we're using it, the getPostMeta method will use it
            // internally.
            
            $postMeta[$postMetaName] = $this->getPostMeta($postId, $postMetaName, '', $single, $transform);
        }
        
        // just in case someone calls this function on a handler that doesn't
        // have any post meta to retrieve, we'll need to use the null
        // coalescing operator to ensure that we return an empty array in the
        // event that $postMeta is not defined in the above loop.
        
        return $postMeta ?? [];
    }
    
    /**
     * getPostMetaSnapshot
     *
     * Sometimes is important to be sure we use the minimum number of database
     * queries.  This will pull an array from the database in a single query
     * and then transform it and return that array.  It'll only have data to
     * provide if updatePostMetaSnapshot has been used to store these postMeta
     * in the database in this capacity.
     *
     * @param int  $postId
     * @param bool $transform
     *
     * @return array
     */
    public function getPostMetaSnapshot (int $postId, bool $transform = true): array
    {
        // just like singular post meta that we might select above, we might have
        // an in-memory cache of our complete post meta set.  if so, we'll want to
        // use it to cut down on database queries.
        
        $snapshotName = $this->getPostMetaSnapshotName();
        if ($this->isPostMetaCached($postId, $snapshotName)) {
            return $this->getCachedPostMeta($postId, $snapshotName);
        }
        
        // if we didn't have a cached version of our postMeta, we'll select
        // them from the database.  then, we loop ovr them and transform each
        // value if necessary.  because we might loop after our selection, we
        // default to an empty array if we've not previously saved a snapshot
        // for these post meta.
        
        $snapshot = $this->retrievePostMeta($postId, $snapshotName, []);
        if ($transform && $this->hasTransformer()) {
            
            // as long as we want to transform and have a transformer, we'll go
            // for it.  notice that the $value variable within our loop is a
            // reference. thus, when we're done, we will have actually
            // transformed the array we return below.
            
            foreach ($snapshot as $postMeta => &$value) {
                $value = $this->transformer->transformFromStorage($postMeta, $value);
            }
        }
        
        $this->maybeCachePostMeta($postId, $snapshotName, $snapshot);
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
    protected function getPostMetaSnapshotName (): string
    {
        if ($this->postMetaSnapshotName !== null) {
            
            // if we've already done the work below, we don't need to do it
            // again.  sure, we're only saving fractions of seconds but maybe
            // every little bit counts, and for a big array of postMeta, the
            // join and hashing operation below could be expensive.
            
            return $this->postMetaSnapshotName;
        }
        
        // to try and make a automatic and repeatably generated post meta name,
        // we'll create the sha1 hash of our post meta names and add our prefix
        // so that a human will be able to see and recognize the hash as being
        // linked to the rest of this handler's data.  a programmer can always
        // override this if necessary.
        
        $hashedNames = sha1(join('', $this->getPostMetaNames()));
        $snapshotName = $this->getPostMetaNamePrefix() . $hashedNames;
        
        // for option names, the codex tells us not to exceed 64 characters for
        // option names (even though the column has a type of VARCHAR(191)).
        // but, for post meta, no such limit is mentioned.  the meta key column
        // has at type of VARCHAR(255) but we'll stick with 64 for some parity
        // between this and the other trait.  finally, notice that we add an
        // underscore in front of this meta key; that's to make sure it's
        // hidden (see http://tiny.cc/55i3iz for more information).
        
        $snapshotName = '_' . substr($snapshotName, 0, 63);
        return ($this->postMetaSnapshotName = $snapshotName);
    }
    
    /**
     * updatePostMeta
     *
     * Ensures that we save this postMeta's value using this plugin's postMeta
     * prefix before calling the storePostMeta method and returning its results.
     *
     * @param int    $postId
     * @param string $postMeta
     * @param mixed  $value
     * @param mixed  $prevValue
     * @param bool   $transform
     *
     * @return bool
     * @throws HandlerException
     */
    public function updatePostMeta (int $postId, string $postMeta, $value, $prevValue = '', bool $transform = true): bool
    {
        // since we transform our $value before we cram it in the database,
        // it's easier for us to (maybe) add it to our cache first.  that way,
        // we have the value the visitor sent us in memory and we don't have to
        // remember to un-transform it before using it elsewhere.
        
        $this->maybeCachePostMeta($postId, $postMeta, $value);
    
        // it's still hard to make a trait know about the methods that are
        // available in the classes in which it might be used.  so, we won't
        // use the isDebug method here, we'll just execute the same command
        // that it does with respect to the WP_DEBUG constant.
    
        if ($this->isPostMetaValid($postMeta, defined('WP_DEBUG') && WP_DEBUG)) {
            $value = $transform && $this->hasTransformer()
                ? $this->transformer->transformForStorage($postMeta, $value)
                : $value;
            
            $fullPostMetaName = $this->getPostMetaNamePrefix() . $postMeta;
            return $this->storePostMeta($postId, $fullPostMetaName, $value, $prevValue);
        }
        
        return false;
    }
    
    /**
     * storePostMeta
     *
     * Stores a value in the database.  Separated from other scopes so this
     * behavior can be overridden, e.g. for the storage of term meta.
     *
     * @param int    $postId
     * @param string $postMeta
     * @param mixed  $value
     * @param mixed  $prevValue
     *
     * @return bool
     */
    protected function storePostMeta (int $postId, string $postMeta, $value, $prevValue = ''): bool
    {
        return update_post_meta($postId, $postMeta, $value, $prevValue);
    }
    
    /**
     * updateAllPostMeta
     *
     * Like the getAllPostMeta method above, this saves all of our information
     * in one call based on the mapping of post meta names to values
     * represented by the $values parameter.
     *
     * @param int   $postId
     * @param array $values
     * @param bool  $transform
     *
     * @return bool
     * @throws HandlerException
     */
    public function updateAllPostMeta (int $postId, array $values, bool $transform = true): bool
    {
        $success = true;
        foreach ($values as $postMeta => $value) {
            
            // the updatePostMeta method returns true when it updates our
            // post meta.  we Boolean AND that value with the current value of
            // $success which starts as true.  so, as long as updatePostMeta
            // return true, $success will remain set.  but, the first time we
            // hit a problem, it'll be reset and will remain so because false
            // AND anything is false.
            
            $success = $success && $this->updatePostMeta($postId, $postMeta, $value, '', $transform);
        }
        
        return $success;
    }
    
    /**
     * updatePostMetaSnapshot
     *
     * To reduce the number of database calls, this method saves all of this
     * handlers post meta in a single database entry.
     *
     * @param int   $postId
     * @param array $values
     * @param bool  $transform
     *
     * @return bool
     * @throws HandlerException
     */
    public function updatePostMetaSnapshot (int $postId, array $values, bool $transform = true): bool
    {
        // since we're about to transform our values for storage, it's easier
        // for us to maybe store them in the cache first, then transform, then
        // update the database.  then, we also update the record of all of our
        // post meta in the cache as well.  finally, we update this information
        // in the individual post meta as well so that the snapshot records
        // matches.
    
        $snapshotName = $this->getPostMetaSnapshotName();
        $this->maybeCachePostMeta($postId, $snapshotName, $values);
        $this->updateAllPostMeta($postId, $values, $transform);
        
        if ($transform && $this->hasTransformer()) {
            
            // if we want to transform and have a transformer, we'll go for it.
            // note that $value is a reference, so the changes we make within
            // the loop will remain when it completes.
            
            foreach ($values as $postMeta => &$value) {
                $value = $this->transformer->transformForStorage($postMeta, $value);
            }
        }
        
        return $this->storePostMeta($postId, $snapshotName, $values);
    }
    
    /**
     * postMetaValueMatches
     *
     * Returns true if the $postMeta's value in the database matches $value.
     * This is useful when determining whether or not an update to this postMeta
     * is necessary.
     *
     * @param string $postMeta
     * @param mixed  $value
     * @param bool   $transform
     *
     * @return bool
     * @throws HandlerException
     */
    public function postMetaValueMatches (string $postMeta, $value, bool $transform = true): bool
    {
        // we don't want our handler to transform the value of $field as it
        // comes out of the database.  doing so would likely mean that it would
        // become different from $value causing the system to try and update
        // things even if it doesn't have to.  hence, we pass a false-flag to
        // the getPostMeta method which prevents it from performing its
        // transformations.
        
        return $this->getPostMeta($postMeta, '', $transform) === $value;
    }
}

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
trait NetworkOptionsManagementTrait
{
    use OptionsManagementTrait;
    
    /**
     * retrieveOption
     *
     * Overrides the single-site based method of the OptionsManagement trait
     * so that this trait retrieves network options.
     *
     * @param string $option
     * @param mixed  $default
     *
     * @return mixed
     */
    protected function retrieveOption (string $option, $default = '')
    {
        return get_site_option($option, $default);
    }
    
    /**
     * storeOption
     *
     * Overrides the single-site based method of the OptionsManagement trait
     * so that this trait stores network options.
     *
     * @param string $option
     * @param mixed  $value
     *
     * @return bool
     */
    protected function storeOption (string $option, $value): bool
    {
        return update_site_option($option, $value);
    }
    
    
}

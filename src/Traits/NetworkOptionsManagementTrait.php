<?php

namespace Dashifen\WPHandler\Traits;

/**
 * Trait NetworkOptionsManagementTrait
 *
 * This trait uses the OptionsManagementTrait and overrides two methods so that
 * it manages network options rather than single-site ones.  However, this does
 * mean that the traits are incompatible.  Therefore, it is recommended that
 * you create two objects, one to manage single-site options and one for
 * network options, if you're working on a plugin that requires both.
 *
 * @package Dashifen\WPHandler\Traits
 */
trait NetworkOptionsManagementTrait
{
    use OptionsManagementTrait;
    
    /**
     * retrieveOption
     *
     * Overrides the trait's method of the same name to retrieve a network
     * option value rather than one for a specific site.
     *
     * @param string $option
     * @param mixed  $defaultValue
     *
     * @return mixed
     */
    protected function retrieveOption (string $option, $defaultValue = '')
    {
        return get_site_option($option, $defaultValue);
    }
    
    /**
     * storeOption
     *
     * Overrides the trait's method of the same name to store a network option
     * rather than one for a specific site.
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

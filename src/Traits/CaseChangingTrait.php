<?php

namespace Dashifen\WPHandler\Traits;

/**
 * Trait CaseChangingTrait
 *
 * Because we prefer kebab-case in some situations, camelCase in others,
 * StudlyCaps in still others, and we might even want a human readable format
 * somewhere, this trait encapsulates ways to convert strings between different
 * types of programming cases.  All parameters are assumed to be in kebab-case.
 *
 * @package Dashifen\WPHandler\Traits
 */
trait CaseChangingTrait
{
    /**
     * kebabToCamelCase
     *
     * Converts a kebab-case HTML attribute value into a camelCase string.
     * Thus, kebab-case becomes kebabCase.
     *
     * @param string $kebabCase
     *
     * @return string
     */
    protected function kebabToCamelCase(string $kebabCase): string
    {
        // to convert from kebab-case to camelCase we look for any letter
        // following a hyphen.  then, our callback function capitalizes it.
        
        $pattern = "/-([a-z])/";
        $function = function (array $matches): string {
            return strtoupper($matches[1]);
        };
        
        return preg_replace_callback($pattern, $function, $kebabCase);
    }
    
    /**
     * kebabToStudlyCase
     *
     * Given a kebab-case string, converts it first to camelCase and then
     * uses ucfirst() to capitalize the first letter, too.  Thus, kebab-case
     * becomes KebabCase.
     *
     * @param string $kebabCase
     *
     * @return string
     */
    protected function kebabToStudlyCase(string $kebabCase): string
    {
        return ucfirst($this->kebabToCamelCase($kebabCase));
    }
    
    /**
     * kebabToReadableCase
     *
     * Turns a kebab-case string into a human readable one.  Thus, kebab-case
     * becomes Kebab Case or kebab case based on the state of the $capitalize
     * parameter.
     *
     * @param string $kebabCase
     * @param bool   $capitalize
     *
     * @return string
     */
    protected function kebabToReadableCase(string $kebabCase, bool $capitalize = true): string
    {
        $string = str_replace('-', ' ', $kebabCase);
        return $capitalize ? ucwords($string) : $string;
    }
}

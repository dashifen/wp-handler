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
    
    /**
     * camelToKebabCase
     *
     * Converts a camel case string into a kebab case one.  Thus, camelCase
     * becomes camel-case.
     *
     * @param string $camelCase
     *
     * @return string
     */
    protected function camelToKebabCase (string $camelCase): string
    {
        // to make this conversion, we want to convert a lowercase character
        // followed by a capital one to the same two lowercase characters
        // separated by a dash.  a regular expression can identify where we
        // want to make our replacement, and a callback function can handle
        // the rest.
        
        $pattern = '/([a-z])([A-Z])/';
        $function = function(array $matches): string {
            
            // $matches[1] is the first letter matched, the lowercase one.
            // $matches[2] is the capitalized one.  so, we strtolower the
            // second one and mash them all together again separated by a dash.
            
            return $matches[1] . '-' . strtolower($matches[2]);
        };
        
        return preg_replace_callback($pattern, $function, $camelCase);
    }
    
    /**
     * camelToStudlyCase
     *
     * Converts a camel case string to a studly case one.  Thus, camelCase
     * becomes CamelCase.
     *
     * @param string $camelCase
     *
     * @return string
     */
    protected function camelToStudlyCase (string $camelCase): string
    {
        return $this->kebabToStudlyCase($this->camelToKebabCase($camelCase));
    }
    
    /**
     * camelToReadableCase
     *
     * Converts a camel case string to a more human readable case.  Thus,
     * camelCase becomes Camel Case, with capitalization controlled by the
     * second parameter.
     *
     * @param string $camelCase
     * @param bool   $capitalize
     *
     * @return string
     */
    protected function camelToReadableCase (string $camelCase, bool $capitalize = true): string
    {
        return $this->kebabToReadableCase($this->camelToKebabCase($camelCase), $capitalize);
    }
}

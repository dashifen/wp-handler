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
    $function = fn ($matches) => strtoupper($matches[1]);
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
  protected function camelToKebabCase(string $camelCase): string
  {
    // to make this conversion, we want to convert a lowercase character
    // followed by a capital one to the same two lowercase characters
    // separated by a dash.  a regular expression can identify where we
    // want to make our replacement, and a callback function can handle
    // the rest by joining the lower case letter with a dash and the newly
    // lower case capital letter.
    
    $pattern = '/([a-z])([A-Z])/';
    $function = fn ($matches) => $matches[1] . '-' . strtolower($matches[2]);
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
  protected function camelToStudlyCase(string $camelCase): string
  {
    return ucfirst($camelCase);
  }
  
  /**
   * camelToReadableCase
   *
   * Converts a camel case string to a more human readable case.  Thus,
   * camelCase becomes Camel Case or camel case, with capitalization controlled
   * by the second parameter.
   *
   * @param string $camelCase
   * @param bool   $capitalize
   *
   * @return string
   */
  protected function camelToReadableCase(string $camelCase, bool $capitalize = true): string
  {
    return $this->kebabToReadableCase(
      $this->camelToKebabCase($camelCase),
      $capitalize
    );
  }
  
  /**
   * studlyToKebabCase
   *
   * Converts a StudlyCaps string to a kebab-case one.  Thus, StudlyCaps would
   * become studly-caps instead.
   *
   * @param string $studlyCase
   *
   * @return string
   */
  protected function studlyToKebabCase(string $studlyCase): string
  {
    // first we convert StudlyCase to camelCase and then we can convert from
    // camelCase to kebab-case.  all of this work is performed by other methods
    // of this trait as follows.
    
    return $this->camelToKebabCase($this->studlyToCamelCase($studlyCase));
  }
  
  /**
   * studlyCaseToCamelCase
   *
   * Given a StudlyCase string, converts it to camelCase.
   *
   * @param string $studlyCase
   *
   * @return string
   */
  protected function studlyToCamelCase(string $studlyCase): string
  {
    // camelCase is the same as StudlyCase except the first character is lower
    // case in the former and capitalized in the latter.  so, to go from studly
    // to camel, we need to lower case the first letter of our parameter.  PHP
    // has a function for that:
    
    return lcfirst($studlyCase);
  }
  
  /**
   * studlyToReadableCase
   *
   * Turns a StudlyCaps string into a more human readable format allowing for
   * both "Studly Caps" and "studly caps" based on the Boolean value of the
   * second parameter.
   *
   * @param string $studlyCase
   * @param bool   $capitalize
   *
   * @return string
   */
  protected function studlyToReadableCase(string $studlyCase, bool $capitalize = true): string
  {
    // first, we switch to camelCase and then from camelCase to our more
    // readable case both using other methods of this trait.  the
    // capitalization of our final case is based on the second parameter.
    
    return $this->camelToReadableCase(
      $this->studlyToCamelCase($studlyCase),
      $capitalize
    );
  }
}

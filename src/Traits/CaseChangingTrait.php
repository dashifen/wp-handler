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
trait CaseChangingTrait {
  /**
   * toCamelCase
   *
   * Converts a kebab-case HTML attribute value into a camelCase string.
   *
   * @param string $kebabCase
   *
   * @return string
   */
  public static function toCamelCase (string $kebabCase): string {
    return preg_replace_callback("/-([a-z])/", function (array $matches): string {
      return strtoupper($matches[1]);
    }, $kebabCase);
  }

  /**
   * toStudlyCaps
   *
   * Given a kebab-case string, converts it first to camelCase and then
   * uses ucfirst() to capitalize the first letter, too.
   *
   * @param string $kebabCase
   *
   * @return string
   */
  public static function toStudlyCaps (string $kebabCase): string {
    return ucfirst(self::toCamelCase($kebabCase));
  }

  /**
   * makeReadable
   *
   * Turns a kebab-case string into a human readable one.
   *
   * @param string $kebabCase
   *
   * @return string
   */
  public static function makeReadable (string $kebabCase): string {
    return ucwords(str_replace('-', ' ', $kebabCase));
  }
}
<?php

namespace Dashifen\WPHandler\Commands\Arguments;

use Dashifen\Repository\RepositoryException;
use Dashifen\WPHandler\Repositories\Arguments\AbstractArgument;
use Dashifen\WPHandler\Repositories\Arguments\ArgumentException;

/**
 * Class AssociativeArgument<?php

namespace Dashifen\WPHandler\Commands\Arguments;

use Dashifen\Repository\RepositoryException;
use Dashifen\WPHandler\Repositories\Arguments\AbstractArgument;
use Dashifen\WPHandler\Repositories\Arguments\ArgumentException;

/**
 * Class AssociativeArgument
 *
 * @property-read string $type
 * @property-read string $name
 * @property-read string $description
 * @property-read array  $options
 * @property-read string $default
 * @property-read bool   $repeating
 * @property-read bool   $optional
 *
 * @package Dashifen\WpCliSuite\Commands\Synopses\Arguments
 */
class AssociativeArgument extends AbstractArgument
{
  /**
   * AssociativeArgument constructor.
   *
   * @param string $name
   * @param string $description
   * @param string $default
   * @param ?array $options
   * @param bool   $repeating
   *
   * @throws RepositoryException
   * @throws ArgumentException
   */
  public function __construct(string $name, string $description = '', string $default = '', ?array $options = null, bool $repeating = false)
  {
    parent::__construct([
      'name'        => $name,
      'type'        => 'assoc',
      'description' => $description,
      'options'     => $options,     // null options means all values are valid
      'default'     => $default,     // default will be come first option as needed
      
      // honestly, we're not sure if WP even allows repeating associative
      // arguments, but nothing in the docs say that they can't repeat, so
      // we'll allow it.
      
      'repeating' => $repeating,
    ]);
  }
}

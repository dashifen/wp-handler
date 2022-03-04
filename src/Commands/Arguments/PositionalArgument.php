<?php

namespace Dashifen\WPHandler\Commands\Arguments;

use Dashifen\Repository\RepositoryException;
use Dashifen\WPHandler\Repositories\Arguments\AbstractArgument;
use Dashifen\WPHandler\Repositories\Arguments\ArgumentException;

/**
 * Class PositionalArgument
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
class PositionalArgument extends AbstractArgument
{
  /**
   * PositionalArgument constructor.
   *
   * @param string $name
   * @param string $description
   * @param bool   $repeating
   *
   * @throws RepositoryException
   * @throws ArgumentException
   */
  public function __construct(string $name, string $description = '', bool $repeating = false)
  {
    parent::__construct([
      'name'        => $name,
      'type'        => 'positional',
      'description' => $description,
      'repeating'   => $repeating,
      'optional'    => false,
    ]);
  }
}

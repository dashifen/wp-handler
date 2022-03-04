<?php

namespace Dashifen\WPHandler\Repositories\Arguments;

use Dashifen\Repository\RepositoryInterface;

/**
 * Class Synopsis
 *
 * @property-read string $type
 * @property-read string $name
 * @property-read string $description
 * @property-read array  $options
 * @property-read string $default
 * @property-read bool   $repeating
 * @property-read bool   $optional
 *
 * @package Dashifen\WpCliSuite\Commands\Synopses
 */
interface ArgumentInterface extends RepositoryInterface
{
  // there's actually nothing to do here, but our ArgumentCollection works
  // best with an interface rather than the abstract class so we made this for
  // it.
}

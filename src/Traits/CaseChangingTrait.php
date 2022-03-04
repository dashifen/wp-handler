<?php

namespace Dashifen\WPHandler\Traits;

/**
 * Trait CaseChangingTrait
 *
 * This is here because anything that depending on the WP Handler package in
 * the past may expect it to be here.  We've extracted the CaseChangingTrait
 * and moved to it's own package, but to avoid breaking a bunch of stuff that
 * we'll "copy" it here as follows.  This file will be removed in version 11.
 *
 * @package Dashifen\WPHandler\Traits
 */
trait CaseChangingTrait {
  use \Dashifen\CaseChangingTrait\CaseChangingTrait;
}

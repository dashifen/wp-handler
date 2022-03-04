<?php

namespace Dashifen\WPHandler\Traits;

use DateTime;
use Exception;
use DateTimeZone;

trait FormattedDateTimeTrait
{
  /**
   * getFormattedDate
   *
   * Returns either the current date in the WordPress date format or the
   * timestamp therein.
   *
   * @param int|null    $timestamp
   * @param string|null $timezone
   *
   * @return string
   */
  protected function getFormattedDate(?int $timestamp = null, ?string $timezone = null): string
  {
    return date(get_option('date_format'), $this->getTimestamp($timestamp, $timezone));
  }
  
  /**
   * getTimestamp
   *
   * Returns either the current timestamp or the specified one in either the
   * timezone specified in the WP settings or the one specified here.
   *
   * @param int|null    $timestamp
   * @param string|null $timezone
   *
   * @return int
   */
  private function getTimestamp(?int $timestamp, ?string $timezone): int
  {
    return ($timestamp ?? time()) + $this->convertTimezoneToOffset($timezone);
  }
  
  /**
   * convertTimezoneToOffset
   *
   * Given a timezone string, uses it or the WP settings to determine the
   * offset from UTC for that timezone in seconds.
   *
   * @param string|null $timezone
   *
   * @return int
   */
  private function convertTimezoneToOffset(?string $timezone): int
  {
    // if the timezone parameter is null, then we want to get the timezone
    // string out of the WP settings.  if that, too, is empty, we'll see if
    // the site admins have specified a GMT offset.  if not even that is
    // specified, then we'll default to the PHP default.  regardless, when
    // we're done here, we want to return the UTC offset in seconds.
    
    $offset = null;
    
    if ($timezone === null) {
      $timezone = get_option('timezone_string');
      if (empty($timezone)) {
        $offset = get_option('gmt_offset');
        if ($offset === '') {
          $timezone = date_default_timezone_get();
        }
      }
    }
    
    // if $offset is now not null, we can skip converting a timezone string
    // into that offset; it must have been changed in the blocks above.
    // otherwise, we work with $timezone to produce a new $offset.
    
    if ($offset !== null) {
      
      // the only way that $offset is not null is if it was set in the
      // if-blocks at the start of this method.  if that's the case, it's
      // in hours because that's the unit WP specifies.  therefore, we
      // convert it to seconds to match the "Z" format used below.
      
      $offset *= 3600;
    } else {
      try {
        $time = new DateTime('now', new DateTimeZone($timezone));
        $offset = $time->format('Z');
      } catch (Exception $e) {
        
        // an exception is thrown by the DateTime constructor when the
        // format string cannot be parsed.  in this case, the 'now'
        // string should always be parsable, so we shouldn't end up
        // here.  but, if we do, all we can do is trigger an error and
        // hope a human can follow up.
        
        trigger_error('Unable to construct DateTime object', E_USER_ERROR);
      }
    }
    
    return $offset;
  }
  
  /**
   * getFormattedTime
   *
   * Returns either the current time in the WordPress time format or the
   * timestamp therein.
   *
   * @param int|null    $timestamp
   * @param string|null $timezone
   *
   * @return string
   */
  protected function getFormattedTime(?int $timestamp = null, ?string $timezone = null): string
  {
    return date(get_option('time_format'), $this->getTimestamp($timestamp, $timezone));
  }
  
  /**
   * getFormattedDateTime
   *
   * Uses the prior to methods to return the current date and time in the
   * WordPress format for each or the timestamp therein.
   *
   * @param int|null    $timestamp
   * @param string|null $timezone
   *
   * @return string
   */
  protected function getFormattedDateTime(?int $timestamp = null, ?string $timezone = null): string
  {
    return sprintf(
      '%s at %s',
      $this->getFormattedDate($timestamp, $timezone),
      $this->getFormattedTime($timestamp, $timezone)
    );
  }
}

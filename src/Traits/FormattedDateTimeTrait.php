<?php

namespace Dashifen\WPHandler\Traits;

trait FormattedDateTimeTrait
{
    /**
     * getFormattedDate
     *
     * Returns either the current date in the WordPress date format or the
     * timestamp therein.
     *
     * @param int|null $timestamp
     *
     * @return string
     */
    protected function getFormattedDate (?int $timestamp = null): string
    {
        return date(get_option('date_format'), $timestamp ?? time());
    }
    
    /**
     * getFormattedTime
     *
     * Returns either the current time in the WordPress time format or the
     * timestamp therein.
     *
     * @param int|null $timestamp
     *
     * @return string
     */
    protected function getFormattedTime (?int $timestamp = null): string
    {
        return date(get_option('time_format'), $timestamp ?? time());
    }
    
    /**
     * getFormattedDateTime
     *
     * Uses the prior to methods to return the current date and time in the
     * WordPress format for each or the timestamp therein.
     *
     * @param int|null $timestamp
     *
     * @return string
     */
    protected function getFormattedDateTime (?int $timestamp = null): string
    {
        return sprintf('%s at %s',
            $this->getFormattedDate($timestamp),
            $this->getFormattedTime($timestamp));
    }
}

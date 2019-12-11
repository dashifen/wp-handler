<?php

namespace Dashifen\WPHandler\Handlers\Themes;

use Dashifen\WPHandler\Handlers\HandlerInterface;

/**
 * Interface HandlerInterface
 *
 * @package Dashifen\WPHandler\Handlers\Themes
 */
interface ThemeHandlerInterface extends HandlerInterface
{
    /**
     * getUrl
     *
     * Returns the URL that corresponds to the folder in which this Handler
     * is located.
     *
     * @return string
     */
    public function getStylesheetUrl(): string;
    
    /**
     * getDir
     *
     * Returns the filesystem path to the folder in which this Handler
     * is located.
     *
     * @return string
     */
    public function getStylesheetDir(): string;
    
    
}

<?php

/**
 * @package Dovetail
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Dovetail;

interface Loader
{
    /**
     * Load config Repository from Finder Manifest
     */
    public function loadConfig(
        Manifest $manifest
    ): Repository;

    /**
     * Save config Repository to Finder Manifest
     */
    public function saveConfig(
        Manifest $manifest,
        Template $data
    ): void;
}

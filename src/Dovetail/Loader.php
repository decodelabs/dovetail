<?php

/**
 * @package Dovetail
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Dovetail;

interface Loader
{
    public function loadConfig(
        Manifest $manifest
    ): Repository;

    public function saveConfig(
        Manifest $manifest,
        Template $data
    ): void;
}

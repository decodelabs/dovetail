<?php

/**
 * Dovetail
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Dovetail;

interface Finder
{
    public function findConfig(
        string $name
    ): Manifest;
}

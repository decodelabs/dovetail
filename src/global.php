<?php

/**
 * @package Dovetail
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

/**
 * global helpers
 */

namespace DecodeLabs\Dovetail
{
    use DecodeLabs\Dovetail;
    use DecodeLabs\Veneer;

    // Register the Veneer facade
    Veneer::register(Context::class, Dovetail::class);
}

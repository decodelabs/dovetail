<?php

/**
 * @package Dovetail
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Dovetail\Tests;

use DecodeLabs\Dovetail\Config;
use DecodeLabs\Dovetail\ConfigTrait;

class AnalyzeConfigTrait implements Config
{
    use ConfigTrait;

    public static function getDefaultValues(): array
    {
        return [
            'foo' => 'bar',
            'baz' => 'bat'
        ];
    }
}

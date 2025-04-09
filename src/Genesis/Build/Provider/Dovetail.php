<?php

/**
 * @package Dovetail
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Genesis\Build\Provider;

use DecodeLabs\Atlas\Dir;
use DecodeLabs\Genesis\Build\Provider;
use Generator;

class Dovetail implements Provider
{
    public string $name = 'dovetail';

    public function __construct()
    {
    }

    public function scanBuildItems(
        Dir $rootDir
    ): Generator {
        yield $rootDir->getFile('.env') => '.env';
        yield $rootDir->getDir('config') => 'config/';
    }
}

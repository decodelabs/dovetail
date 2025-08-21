<?php

/**
 * @package Dovetail
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Dovetail\Finder;

use DecodeLabs\Dovetail\Finder;
use DecodeLabs\Dovetail\Format;
use DecodeLabs\Dovetail\Manifest;

class Generic implements Finder
{
    public function __construct(
        protected string $rootPath,
        protected string $configPath = 'config'
    ) {
    }

    public function findConfig(
        string $name
    ): Manifest {
        return new Manifest(
            $name,
            $this->rootPath . '/' . $this->configPath . '/' . $name . '.php',
            Format::Php
        );
    }
}

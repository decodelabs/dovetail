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
    /**
     * Init with app root path and config directory name
     */
    public function __construct(
        protected string $rootPath,
        protected string $configPath = 'config'
    ) {
    }

    /**
     * Find .env file
     */
    public function findEnv(): ?Manifest
    {
        return new Manifest('env', $this->rootPath . '/.env', Format::DotEnv);
    }

    /**
     * Find PHP config file
     */
    public function findConfig(string $name): Manifest
    {
        return new Manifest(
            $name,
            $this->rootPath . '/' . $this->configPath . '/' . $name . '.php',
            Format::PHP
        );
    }
}

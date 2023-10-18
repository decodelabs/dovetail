<?php

/**
 * @package Dovetail
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Dovetail;

interface Finder
{
    public function findEnv(): ?Manifest;
    public function findConfig(string $name): Manifest;
}

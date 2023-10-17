<?php

/**
 * @package Dovetail
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Dovetail;

interface Config
{
    /**
     * @return array<int|string, bool|float|int|array<mixed>|string|null>
     */
    public static function getDefaultValues(): array;

    public function __construct(
        Manifest $manifest,
        Repository $data
    );

    public function getConfigManifest(): Manifest;
    public function getConfigRepository(): Repository;
}

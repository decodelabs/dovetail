<?php

/**
 * @package Dovetail
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Dovetail;

use DecodeLabs\Dovetail;
use DecodeLabs\Fluidity\CastTrait;

trait ConfigTrait
{
    use CastTrait;

    protected Repository $data;
    protected Manifest $manifest;

    public static function load(): static
    {
        return Dovetail::load(static::getRepositoryName())
            ->as(static::class);
    }

    public static function getRepositoryName(): string
    {
        $parts = explode('\\', static::class);
        return array_pop($parts);
    }

    public function __construct(
        Manifest $manifest,
        Repository $data
    ) {
        $this->manifest = $manifest;
        $this->data = $data;
    }

    public function getConfigManifest(): Manifest
    {
        return $this->manifest;
    }

    public function getConfigRepository(): Repository
    {
        return $this->data;
    }
}

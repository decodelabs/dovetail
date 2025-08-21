<?php

/**
 * @package Dovetail
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Dovetail;

use DecodeLabs\Dovetail;
use DecodeLabs\Fluidity\CastTrait;
use DecodeLabs\Kingdom\ContainerAdapter;
use DecodeLabs\Monarch;
use ReflectionClass;

/**
 * @phpstan-require-implements Config
 */
trait ConfigTrait
{
    use CastTrait;

    protected Repository $data;
    protected Manifest $manifest;

    public static function provideService(
        ContainerAdapter $container
    ): static {
        $dovetail = $container->get(Dovetail::class);
        return $dovetail->load(static::class);
    }

    public static function getRepositoryName(): string
    {
        return new ReflectionClass(static::class)->getShortName();
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

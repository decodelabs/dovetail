<?php

/**
 * @package Dovetail
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Dovetail;

trait ConfigTrait
{
    protected Repository $data;
    protected Manifest $manifest;

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

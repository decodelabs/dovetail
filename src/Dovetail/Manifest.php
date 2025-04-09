<?php

/**
 * @package Dovetail
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Dovetail;

class Manifest
{
    public function __construct(
        protected string $name,
        protected string $path,
        protected Format $format
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function exists(): bool
    {
        return is_file($this->path);
    }

    public function getFormat(): Format
    {
        return $this->format;
    }

    public function isFormat(
        string|Format $format
    ): bool {
        return $this->format->is($format);
    }

    public function getLoaderName(): string
    {
        return $this->format->value;
    }
}

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
        protected(set) string $name,
        protected(set) string $path,
        protected(set) Format $format
    ) {
    }

    public function exists(): bool
    {
        return is_file($this->path);
    }

    public function isFormat(
        string|Format $format
    ): bool {
        return $this->format->is($format);
    }
}

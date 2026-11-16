<?php

/**
 * Dovetail
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Dovetail;

class Manifest
{
    public function __construct(
        public protected(set) string $name,
        public protected(set) string $path,
        public protected(set) Format $format
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

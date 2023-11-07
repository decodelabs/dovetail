<?php

/**
 * @package Dovetail
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Dovetail;

enum Format: string
{
    case DotEnv = 'DotEnv';
    case PHP = 'PHP';

    public function is(
        string|Format $format
    ): bool {
        return is_string($format) ?
            $this->value === $format :
            $this->value === $format->value;
    }
}

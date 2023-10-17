<?php

/**
 * @package Dovetail
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Dovetail\Loader;

use DecodeLabs\Dovetail\Loader;
use DecodeLabs\Dovetail\Manifest;
use DecodeLabs\Dovetail\Repository;

class PHP implements Loader
{
    /**
     * Load PHP array config
     */
    public function loadConfig(Manifest $manifest): Repository
    {
        $data = require $manifest->getPath();

        if (is_iterable($data)) {
            return new Repository($data);
        } else {
            return new Repository(null, $data);
        }
    }

    /**
     * Save PHP array config
     */
    public function saveConfig(
        Manifest $manifest,
        array $data
    ): void {
        $output = static::exportArray($data);
        $output = '<?php' . "\n\n" . 'return ' . $output . ';';

        file_put_contents($manifest->getPath(), $output);
    }


    /**
     * Export array to PHP
     *
     * @param array<int|string, bool|float|int|array<mixed>|string|null> $values
     */
    protected static function exportArray(
        array $values,
        int $level = 1
    ): string {
        $output = '[' . "\n";

        $i = 0;
        $count = count($values);
        $isNumericIndex = true;

        foreach ($values as $key => $val) {
            if ($key !== $i++) {
                $isNumericIndex = false;
                break;
            }
        }

        $i = 0;

        foreach ($values as $key => $val) {
            $output .= str_repeat('    ', $level);

            if (!$isNumericIndex) {
                $output .= '\'' . addslashes((string)$key) . '\' => ';
            }

            if (is_null($val)) {
                $output .= 'null';
            } elseif (is_array($val)) {
                /** @var array<int|string, bool|float|int|array<mixed>|string|null> $val */
                $output .= self::exportArray($val, $level + 1);
            } elseif (
                is_int($val) ||
                is_float($val)
            ) {
                $output .= $val;
            } elseif (is_bool($val)) {
                $output .= $val ? 'true' : 'false';
            } else {
                $output .= '\'' . addslashes($val) . '\'';
            }

            if (++$i < $count) {
                $output .= ',';
            }

            $output .= "\n";
        }

        $output .= str_repeat('    ', $level - 1) . ']';
        return $output;
    }
}

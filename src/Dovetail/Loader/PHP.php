<?php

/**
 * @package Dovetail
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Dovetail\Loader;

use DecodeLabs\Atlas;
use DecodeLabs\Dovetail\Loader;
use DecodeLabs\Dovetail\Manifest;
use DecodeLabs\Dovetail\Repository;
use DecodeLabs\Dovetail\Template;
use DecodeLabs\Dovetail\Template\Resolvable;

class PHP implements Loader
{
    /**
     * Load PHP array config
     */
    public function loadConfig(
        Manifest $manifest
    ): Repository {
        if (!file_exists($manifest->getPath())) {
            $data = [];
        } else {
            $data = require $manifest->getPath();
        }

        if (is_iterable($data)) {
            // @phpstan-ignore-next-line
            return new Repository($data);
        } else {
            // @phpstan-ignore-next-line
            return new Repository(null, $data);
        }
    }

    /**
     * Save PHP array config
     */
    public function saveConfig(
        Manifest $manifest,
        Template $template
    ): void {
        $output = static::exportArray($template->getData());
        $output =
            '<?php' . "\n\n" .
            $template->getUseStatements() .
            'return ' . $output . ';' . "\n";

        Atlas::createFile($manifest->getPath(), $output);
    }


    /**
     * Export array to PHP
     *
     * @param array<int|string, bool|float|int|array<mixed>|string|Resolvable|null> $values
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

            if ($val instanceof Resolvable) {
                $output .= $val->getCode();
            } elseif (is_null($val)) {
                $output .= 'null';
            } elseif (is_array($val)) {
                /** @var array<int|string, bool|float|int|array<mixed>|string|Resolvable|null> $val */
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

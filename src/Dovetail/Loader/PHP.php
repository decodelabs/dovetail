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
    public function loadConfig(Manifest $manifest): Repository
    {
        $data = require $manifest->getPath();

        if (is_iterable($data)) {
            return new Repository($data);
        } else {
            return new Repository(null, $data);
        }
    }
}

<?php

/**
 * @package Dovetail
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Dovetail;

use DecodeLabs\Collections\Tree;

/**
 * @extends Tree<string|int|float|bool>
 */
class Repository extends Tree
{
    /** @var non-empty-string */
    protected const string KeySeparator = '.';
}

<?php

/**
 * @package Dovetail
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Dovetail;

use DecodeLabs\Collections\Tree\NativeMutable as MutableTree;

/**
 * @extends MutableTree<string|int|float|bool|null>
 */
class Repository extends MutableTree
{
    public const KEY_SEPARATOR = '.';
}

<?php

/**
 * @package Dovetail
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Dovetail;

use DecodeLabs\Coercion;
use DecodeLabs\Exceptional;
use DecodeLabs\Iota;
use DecodeLabs\Monarch;
use Dotenv\Dotenv;

class Env
{
    protected static bool $envLoaded = false;

    public static function initialize(
        Iota $iota
    ): void {
        if (self::$envLoaded) {
            return;
        }

        $repo = $iota->loadDynamic('dovetail');
        $data = null;

        if ($repo->has('dotenv')) {
            $data = $repo->return('dotenv');
        }

        /** @var array<string,string> $data */

        if (
            ($data['ENV_MODE'] ?? null) === 'development' &&
            null !== ($time = $repo->getTime('dotenv')) &&
            filemtime(Monarch::getPaths()->run . '/.env') > $time
        ) {
            $repo->remove('dotenv');
            $data = null;
        }


        if ($data !== null) {
            $_ENV = array_merge($_ENV, $data);
            self::$envLoaded = true;
            return;
        }

        $env = Dotenv::createMutable(Monarch::getPaths()->run);

        $data = $env->safeLoad();
        self::$envLoaded = true;

        if (!Monarch::getBuild()->compiled) {
            $repo->storeStaticArray('dotenv', $data);
        }
    }

    protected static function checkInitialized(): void
    {
        if (!self::$envLoaded) {
            self::initialize(
                Monarch::getService(Iota::class)
            );
            /*
            throw Exceptional::Runtime(
                message: 'Environment variables not initialized'
            );
            */
        }
    }

    /**
     * @return array<string,string>
     */
    public static function rebuildCache(
        Iota $iota
    ): array {
        $env = Dotenv::createMutable(Monarch::getPaths()->run);

        /** @var array<string,string> $data */
        $data = $env->safeLoad();

        $_ENV = array_merge($_ENV, $data);
        self::$envLoaded = true;

        $repo = $iota->loadDynamic('dovetail');
        $repo->storeStaticArray('dotenv', $data);

        return $data;
    }


    public static function has(
        string $name
    ): bool {
        self::checkInitialized();
        return isset($_ENV[$name]);
    }

    public static function asString(
        string $name,
        ?string $default = null
    ): string {
        if (null === ($value = self::tryString($name, $default))) {
            throw Exceptional::Runtime(
                message: 'Environment variable "' . $name . '" is not set'
            );
        }

        return $value;
    }

    public static function tryString(
        string $name,
        ?string $default = null
    ): ?string {
        self::checkInitialized();
        return Coercion::tryString($_ENV[$name] ?? $default);
    }

    public static function asInt(
        string $name,
        ?int $default = null
    ): int {
        if (null === ($value = self::tryInt($name, $default))) {
            throw Exceptional::Runtime(
                message: 'Environment variable "' . $name . '" is not set'
            );
        }

        return $value;
    }

    public static function tryInt(
        string $name,
        ?int $default = null
    ): ?int {
        self::checkInitialized();
        return Coercion::tryInt($_ENV[$name] ?? $default);
    }

    public static function asFloat(
        string $name,
        ?float $default = null
    ): float {
        if (null === ($value = self::tryFloat($name, $default))) {
            throw Exceptional::Runtime(
                message: 'Environment variable "' . $name . '" is not set'
            );
        }

        return $value;
    }

    public static function tryFloat(
        string $name,
        ?float $default = null
    ): ?float {
        self::checkInitialized();
        return Coercion::tryFloat($_ENV[$name] ?? $default);
    }

    public static function asBool(
        string $name,
        ?bool $default = null
    ): bool {
        if (null === ($value = self::tryBool($name, $default))) {
            throw Exceptional::Runtime(
                message: 'Environment variable "' . $name . '" is not set'
            );
        }

        return $value;
    }

    public static function tryBool(
        string $name,
        ?bool $default = null
    ): ?bool {
        self::checkInitialized();
        return Coercion::tryBool($_ENV[$name] ?? $default);
    }
}

<?php
/**
 * This is a stub file for IDE compatibility only.
 * It should not be included in your projects.
 */
namespace DecodeLabs;

use DecodeLabs\Veneer\Proxy as Proxy;
use DecodeLabs\Veneer\ProxyTrait as ProxyTrait;
use DecodeLabs\Dovetail\Context as Inst;
use DecodeLabs\Dovetail\Finder as Ref0;
use DecodeLabs\Dovetail\Config as Ref1;
use DecodeLabs\Dovetail\Repository as Ref2;
use DecodeLabs\Dovetail\Manifest as Ref3;
use DecodeLabs\Dovetail\Loader as Ref4;

class Dovetail implements Proxy
{
    use ProxyTrait;

    public const Veneer = 'DecodeLabs\\Dovetail';
    public const VeneerTarget = Inst::class;

    protected static Inst $_veneerInstance;

    public static function setFinder(Ref0 $finder): Inst {
        return static::$_veneerInstance->setFinder(...func_get_args());
    }
    public static function getFinder(): Ref0 {
        return static::$_veneerInstance->getFinder();
    }
    public static function setEnvPath(string $path): Inst {
        return static::$_veneerInstance->setEnvPath(...func_get_args());
    }
    public static function getEnvPath(): string {
        return static::$_veneerInstance->getEnvPath();
    }
    public static function hasEnv(string $name): bool {
        return static::$_veneerInstance->hasEnv(...func_get_args());
    }
    public static function env(array|string $name, string|int|float|bool|null $default = NULL): string|int|float|bool|null {
        return static::$_veneerInstance->env(...func_get_args());
    }
    public static function envString(array|string $name, ?string $default = NULL): ?string {
        return static::$_veneerInstance->envString(...func_get_args());
    }
    public static function envInt(array|string $name, ?int $default = NULL): ?int {
        return static::$_veneerInstance->envInt(...func_get_args());
    }
    public static function envFloat(array|string $name, ?float $default = NULL): ?float {
        return static::$_veneerInstance->envFloat(...func_get_args());
    }
    public static function envBool(array|string $name, ?bool $default = NULL): ?bool {
        return static::$_veneerInstance->envBool(...func_get_args());
    }
    public static function canLoad(string $name, ?string $interface = NULL): bool {
        return static::$_veneerInstance->canLoad(...func_get_args());
    }
    public static function load(string $name): Ref1 {
        return static::$_veneerInstance->load(...func_get_args());
    }
    public static function loadRepository(string $name): ?Ref2 {
        return static::$_veneerInstance->loadRepository(...func_get_args());
    }
    public static function getLoaderFor(Ref3 $manifest): Ref4 {
        return static::$_veneerInstance->getLoaderFor(...func_get_args());
    }
};

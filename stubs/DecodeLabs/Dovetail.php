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

    const Veneer = 'DecodeLabs\\Dovetail';
    const VeneerTarget = Inst::class;

    public static Inst $instance;

    public static function setFinder(Ref0 $finder): Inst {
        return static::$instance->setFinder(...func_get_args());
    }
    public static function getFinder(): Ref0 {
        return static::$instance->getFinder();
    }
    public static function setEnvPath(string $path): Inst {
        return static::$instance->setEnvPath(...func_get_args());
    }
    public static function getEnvPath(): string {
        return static::$instance->getEnvPath();
    }
    public static function hasEnv(string $name): bool {
        return static::$instance->hasEnv(...func_get_args());
    }
    public static function env(array|string $name, string|int|float|bool|null $default = NULL): string|int|float|bool|null {
        return static::$instance->env(...func_get_args());
    }
    public static function envString(array|string $name, ?string $default = NULL): ?string {
        return static::$instance->envString(...func_get_args());
    }
    public static function envInt(array|string $name, ?int $default = NULL): ?int {
        return static::$instance->envInt(...func_get_args());
    }
    public static function envFloat(array|string $name, ?float $default = NULL): ?float {
        return static::$instance->envFloat(...func_get_args());
    }
    public static function envBool(array|string $name, ?bool $default = NULL): ?bool {
        return static::$instance->envBool(...func_get_args());
    }
    public static function canLoad(string $name, ?string $interface = NULL): bool {
        return static::$instance->canLoad(...func_get_args());
    }
    public static function load(string $name): Ref1 {
        return static::$instance->load(...func_get_args());
    }
    public static function loadRepository(string $name): ?Ref2 {
        return static::$instance->loadRepository(...func_get_args());
    }
    public static function getLoaderFor(Ref3 $manifest): Ref4 {
        return static::$instance->getLoaderFor(...func_get_args());
    }
};

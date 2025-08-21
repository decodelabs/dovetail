<?php

/**
 * @package Dovetail
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs;

use DecodeLabs\Archetype;
use DecodeLabs\Archetype\Exception as ArchetypeException;
use DecodeLabs\Dovetail\Config;
use DecodeLabs\Dovetail\Env;
use DecodeLabs\Dovetail\Finder;
use DecodeLabs\Dovetail\Finder\Generic as GenericFinder;
use DecodeLabs\Dovetail\Format;
use DecodeLabs\Dovetail\Loader;
use DecodeLabs\Dovetail\Manifest;
use DecodeLabs\Dovetail\Repository;
use DecodeLabs\Dovetail\Template;
use DecodeLabs\Iota;
use DecodeLabs\Kingdom\ContainerAdapter;
use DecodeLabs\Kingdom\Service;
use DecodeLabs\Kingdom\ServiceTrait;
use DecodeLabs\Monarch;

class Dovetail implements Service
{
    use ServiceTrait;

    public protected(set) Finder $finder;

    /**
     * @var array<string, Config>
     */
    protected array $configs = [];

    protected Archetype $archetype;

    public static function provideService(
        ContainerAdapter $container
    ): static {
        if (!$container->has(Finder::class)) {
            $container->setFactory(
                Finder::class,
                fn () => new GenericFinder(Monarch::getPaths()->run)
            );
        }

        return $container->getOrCreate(static::class);
    }

    public function __construct(
        Finder $finder,
        Archetype $archetype,
        Iota $iota
    ) {
        $this->finder = $finder;
        $this->archetype = $archetype;
        Env::initialize($iota);
    }



    public function canLoad(
        string $name,
        ?string $interface = null
    ): bool {
        if (
            isset($this->configs[$name]) &&
            (
                !$interface ||
                $this->configs[$name] instanceof $interface
            )
        ) {
            return true;
        }

        try {
            $configClass = $this->archetype->resolve(Config::class, $name);
        } catch (ArchetypeException $e) {
            return false;
        }


        return
            !$interface ||
            is_a($configClass, $interface, true)
        ;
    }


    /**
     * @template T of Config
     * @param string|class-string<T> $name
     * @return ($name is class-string<T> ? T : Config)
     */
    public function load(
        string $name
    ): Config {
        if (isset($this->configs[$name])) {
            return $this->configs[$name];
        }

        $configClass = $this->archetype->resolve(Config::class, $name);
        $name = $configClass::getRepositoryName();

        $manifest = $this->finder->findConfig($name);
        $loader = $this->getLoaderFor($manifest);

        if (!$manifest->exists()) {
            $this->initRepository($manifest, $loader, $configClass);
        }

        $repository = $loader->loadConfig($manifest);

        $config = new $configClass($manifest, $repository);
        $this->configs[$name] = $config;

        return $this->configs[$name];
    }

    /**
     * @param class-string<Config> $configClass
     */
    protected function initRepository(
        Manifest $manifest,
        Loader $loader,
        string $configClass
    ): void {
        $data = $configClass::getDefaultValues();

        if (Monarch::isDevelopment()) {
            $loader->saveConfig($manifest, new Template($data));
        }
    }


    public function loadRepository(
        string $name
    ): ?Repository {
        $manifest = $this->finder->findConfig($name);

        if (!$manifest->exists()) {
            return null;
        }

        $loader = $this->getLoaderFor($manifest);
        return $loader->loadConfig($manifest);
    }

    public function getLoaderFor(
        Manifest $manifest
    ): Loader {
        $class = $this->archetype->resolve(Loader::class, $manifest->format->value);
        return new $class();
    }
}

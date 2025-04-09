<?php

/**
 * @package Dovetail
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Dovetail;

use DecodeLabs\Archetype;
use DecodeLabs\Archetype\Exception as ArchetypeException;
use DecodeLabs\Coercion;
use DecodeLabs\Dovetail;
use DecodeLabs\Dovetail\Finder\Generic as GenericFinder;
use DecodeLabs\Exceptional;
use DecodeLabs\Monarch;
use DecodeLabs\Veneer;
use Dotenv\Dotenv;
use Throwable;

class Context
{
    protected ?Finder $finder = null;

    /**
     * @var array<string, Config>
     */
    protected array $configs = [];

    protected ?string $envPath = null;
    protected ?DotEnv $env = null;



    /**
     * Set finder
     *
     * @return $this
     */
    public function setFinder(
        Finder $finder
    ): Context {
        $this->finder = $finder;
        return $this;
    }

    /**
     * Get finder
     */
    public function getFinder(): Finder
    {
        if (!$this->finder) {
            $this->finder = new GenericFinder($this->getEnvPath());
        }

        return $this->finder;
    }


    /**
     * Set env path
     *
     * @return $this
     */
    public function setEnvPath(
        string $path
    ): Context {
        if ($this->env) {
            throw Exceptional::Runtime(
                message: 'Cannot set env path after env has been loaded'
            );
        }

        $this->envPath = $path;
        $this->loadEnv();

        return $this;
    }

    /**
     * Get env path
     */
    public function getEnvPath(): string
    {
        if (!$this->envPath) {
            $this->envPath = $this->detectEnvPath();
        }

        return $this->envPath;
    }

    protected function detectEnvPath(): string
    {
        // Finder
        if (
            $this->finder &&
            ($manifest = $this->finder->findEnv())
        ) {
            if (!$manifest->isFormat(Format::DotEnv)) {
                throw Exceptional::Setup(
                    message: 'Env config must be in DotEnv format'
                );
            }

            return dirname($manifest->getPath());
        }

        return Monarch::$paths->run;
    }

    protected function loadEnv(): void
    {
        if ($this->env) {
            return;
        }

        $this->env = Dotenv::createMutable($this->getEnvPath());
        $this->env->safeLoad();
    }

    /**
     * Env value exists?
     */
    public function hasEnv(
        string $name
    ): bool {
        $this->loadEnv();
        return isset($_ENV[$name]);
    }

    /**
     * Get env value without typing
     *
     * @param string|array<string> $name
     */
    public function env(
        string|array $name,
        string|bool|int|float|null $default = null
    ): string|bool|int|float|null {
        $this->loadEnv();

        if (is_array($name)) {
            foreach ($name as $key) {
                if (isset($_ENV[$key])) {
                    // @phpstan-ignore-next-line
                    return $_ENV[$key];
                }
            }

            return $default;
        }

        /** @var bool|float|int|string|null */
        $output = $_ENV[$name] ?? $default;
        return $output;
    }

    /**
     * Get string env value
     *
     * @param string|array<string> $name
     */
    public function envString(
        string|array $name,
        ?string $default = null
    ): ?string {
        return Coercion::tryString($this->env($name) ?? $default);
    }

    /**
     * Get int env value
     *
     * @param string|array<string> $name
     */
    public function envInt(
        string|array $name,
        ?int $default = null
    ): ?int {
        return Coercion::tryInt($this->env($name) ?? $default);
    }

    /**
     * Get float env value
     *
     * @param string|array<string> $name
     */
    public function envFloat(
        string|array $name,
        ?float $default = null
    ): ?float {
        return Coercion::tryFloat($this->env($name) ?? $default);
    }

    /**
     * Get bool env value
     *
     * @param string|array<string> $name
     */
    public function envBool(
        string|array $name,
        ?bool $default = null
    ): ?bool {
        return Coercion::tryBool($this->env($name) ?? $default);
    }


    /**
     * Can load
     */
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
            $configClass = Archetype::resolve(Config::class, $name);
        } catch (ArchetypeException $e) {
            return false;
        }


        return
            !$interface ||
            is_a($configClass, $interface, true)
        ;
    }


    /**
     * Load config
     *
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

        $configClass = Archetype::resolve(Config::class, $name);
        $name = $configClass::getRepositoryName();

        $manifest = $this->getFinder()->findConfig($name);
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
        $manifest = $this->getFinder()->findConfig($name);

        if (!$manifest->exists()) {
            return null;
        }

        $loader = $this->getLoaderFor($manifest);
        return $loader->loadConfig($manifest);
    }

    /**
     * Get Loader for Manifest
     */
    public function getLoaderFor(
        Manifest $manifest
    ): Loader {
        $class = Archetype::resolve(Loader::class, $manifest->getLoaderName());
        return new $class();
    }
}


// Register Veneer frontage
Veneer\Manager::getGlobalManager()->register(
    Context::class,
    Dovetail::class
);

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
use DecodeLabs\Genesis;
use DecodeLabs\Genesis\Environment;
use DecodeLabs\Veneer;
use DecodeLabs\Veneer\LazyLoad;

use Dotenv\Dotenv;
use Throwable;

#[LazyLoad]
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
                'Cannot set env path after env has been loaded'
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
                throw Exceptional::Setup('Env config must be in DotEnv format');
            }

            return dirname($manifest->getPath());
        }

        if (class_exists(Genesis::class)) {
            try {
                return Genesis::$hub->getApplicationPath();
            } catch(Throwable $e) {
            }
        }

        if (false !== ($path = getcwd())) {
            if (!file_exists($path . '/.env')) {
                $parent = dirname($path);

                if (file_exists($parent . '/.env')) {
                    return $parent;
                }
            }

            return $path;
        }


        throw Exceptional::Runtime(
            'Unable to detect env path'
        );
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
     */
    public function env(
        string $name,
        string|bool|int|float|null $default = null
    ): string|bool|int|float|null {
        $this->loadEnv();
        return $_ENV[$name] ?? $default;
    }

    /**
     * Get string env value
     */
    public function envString(
        string $name,
        ?string $default = null
    ): ?string {
        return Coercion::toStringOrNull($this->env($name) ?? $default);
    }

    /**
     * Get int env value
     */
    public function envInt(
        string $name,
        ?int $default = null
    ): ?int {
        return Coercion::toIntOrNull($this->env($name) ?? $default);
    }

    /**
     * Get float env value
     */
    public function envFloat(
        string $name,
        ?float $default = null
    ): ?float {
        return Coercion::toFloatOrNull($this->env($name) ?? $default);
    }

    /**
     * Get bool env value
     */
    public function envBool(
        string $name,
        ?bool $default = null
    ): ?bool {
        return Coercion::toBoolOrNull($this->env($name) ?? $default);
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

        if (
            class_exists(Genesis::class) &&
            Genesis::$environment instanceof Environment
        ) {
            $development = Genesis::$environment->isDevelopment();
        } else {
            $development = $this->envString('ENV_MODE') === 'development';
        }

        if ($development) {
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
Veneer::register(Context::class, Dovetail::class);

<?php

/**
 * @package Dovetail
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Dovetail;

use DecodeLabs\Archetype;
use DecodeLabs\Coercion;
use DecodeLabs\Dovetail\Finder\Generic as GenericFinder;
use DecodeLabs\Exceptional;
use DecodeLabs\Genesis;
use DecodeLabs\Veneer\LazyLoad;

use Dotenv\Dotenv;

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
    public function setFinder(Finder $finder): Context
    {
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
    public function setEnvPath(string $path): Context
    {
        if ($this->env) {
            throw Exceptional::Runtime(
                'Cannot set env path after env has been loaded'
            );
        }

        $this->envPath = $path;
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
            return Genesis::$hub->getApplicationPath();
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

        $this->env = Dotenv::createImmutable($this->getEnvPath());
        $this->env->safeLoad();
    }

    /**
     * Env value exists?
     */
    public function hasEnv(string $name): bool
    {
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
     * Load config
     */
    public function load(string $name): Config
    {
        if (isset($this->configs[$name])) {
            return $this->configs[$name];
        }

        $resolveName = explode('#', $name)[0];
        $class = Archetype::resolve(Config::class, $resolveName);

        $repository = $this->loadRespository($name);
        $config = new $class($repository);

        $this->configs[$name] = $config;
        return $this->configs[$name];
    }


    public function loadRespository(string $name): ?Repository
    {
        $manifest = $this->getFinder()->findConfig($name);

        if (!$manifest->exists()) {
            return null;
        }

        $class = Archetype::resolve(Loader::class, $manifest->getLoaderName());
        $loader = new $class();
        return $loader->loadConfig($manifest);
    }
}

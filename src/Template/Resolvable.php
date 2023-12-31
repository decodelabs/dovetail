<?php

/**
 * @package Dovetail
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Dovetail\Template;

class Resolvable
{
    protected ?string $namespace = null;
    protected string $code;
    protected string|int|float|null $value;

    /**
     * Parse string value
     */
    public static function parse(
        string $value
    ): string|static {
        if (!preg_match('/^\{\{([^}]+)\}(\:([^}]+))?\}$/i', $value, $matches)) {
            return $value;
        }

        $code = $matches[1];
        $value = $matches[3] ?? null;

        if (is_numeric($value)) {
            $value = (float)$value;
        }

        return new static($code, $value);
    }

    /**
     * Init with code and value
     */
    final public function __construct(
        string $code,
        string|int|float|null $value = null
    ) {
        $this->value = $value;
        $this->parseCode($code);
    }

    /**
     * Split namespace from code
     */
    protected function parseCode(
        string $code
    ): void {
        // env*()
        if (preg_match('/^env(String|Bool|Int|Float)\(?/', $code)) {
            $this->namespace = 'DecodeLabs\\Dovetail';
            $this->code = 'Dovetail::' . $code;
            return;
        }

        // Namespace\Class::method()
        if (preg_match('/^(([a-zA-Z0-9_]+\\\)*([a-zA-Z0-9_]+))\:\:/i', $code, $matches)) {
            $this->namespace = $matches[1];
            $this->code = substr($code, strlen($this->namespace) - strlen($matches[3]));
            return;
        }
    }

    /**
     * Get namespace
     */
    public function getNamespace(): ?string
    {
        return $this->namespace;
    }

    /**
     * Get code
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * Get value
     */
    public function getValue(): string|int|float|null
    {
        return $this->value;
    }
}

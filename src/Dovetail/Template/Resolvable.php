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

    final public function __construct(
        string $code,
        string|int|float|null $value = null
    ) {
        $this->value = $value;
        $this->parseCode($code);
    }

    protected function parseCode(
        string $code
    ): void {
        // env*()
        if (preg_match('/^Env::(string|bool|int|float)\(?/', $code)) {
            $this->namespace = 'DecodeLabs\\Dovetail';
            $this->code = 'Env::' . $code;
            return;
        }

        // Namespace\Class::method()
        if (preg_match('/^(([a-zA-Z0-9_]+\\\)*([a-zA-Z0-9_]+))\:\:/i', $code, $matches)) {
            $this->namespace = $matches[1];
            $this->code = substr($code, strlen($this->namespace) - strlen($matches[3]));
            return;
        }
    }

    public function getNamespace(): ?string
    {
        return $this->namespace;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getValue(): string|int|float|null
    {
        return $this->value;
    }
}

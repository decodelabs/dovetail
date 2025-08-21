<?php

/**
 * @package Dovetail
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Dovetail;

use DecodeLabs\Dovetail\Template\Resolvable;

class Template
{
    /**
     * @var array<string>
     */
    protected array $namespaces = [];

    /**
     * @var array<int|string, bool|float|int|array<mixed>|string|Resolvable|null>
     */
    protected array $data;

    /**
     * @param array<int|string, bool|float|int|array<mixed>|string|null> $data
     */
    public function __construct(
        array $data
    ) {
        $this->data = $this->parse($data);
    }

    /**
     * @param array<int|string, bool|float|int|array<mixed>|string|null> $data
     * @return array<int|string, bool|float|int|array<mixed>|string|Resolvable|null> $data
     */
    protected function parse(
        array $data
    ): array {
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $data[$key] = $r = Resolvable::parse($value);

                if (
                    $r instanceof Resolvable &&
                    null !== ($namespace = $r->getNamespace())
                ) {
                    $this->namespaces[] = $namespace;
                }
            } elseif (is_array($value)) {
                /** @var array<int|string, bool|float|int|array<mixed>|string|null> $value */
                $data[$key] = $this->parse($value);
            }
        }

        return $data;
    }

    /**
     * @return array<string>
     */
    public function getNamespaces(): array
    {
        return $this->namespaces;
    }

    /**
     * @return array<int|string, bool|float|int|array<mixed>|string|Resolvable|null>
     */
    public function getData(): array
    {
        return $this->data;
    }

    public function getUseStatements(): ?string
    {
        $output = [];

        foreach (array_unique($this->namespaces) as $namespace) {
            $output[] = 'use ' . $namespace . ';';
        }

        if (empty($output)) {
            return null;
        }

        return implode("\n", $output) . "\n\n";
    }
}

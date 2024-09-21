<?php

declare(strict_types=1);

namespace Zodimo\Xml\EXI;

class GrammerNotation
{
    private string $notation;

    /**
     * @var array<string>
     */
    private array $parameters;

    /**
     * @param array<string> $parameters
     */
    private function __construct(string $notation, array $parameters)
    {
        $this->notation = $notation;
        $this->parameters = $parameters;
    }

    /**
     * @param array<string> $parameters
     */
    public static function create(string $notation, array $parameters = []): GrammerNotation
    {
        return new GrammerNotation($notation, $parameters);
    }

    public function getNotation(): string
    {
        return $this->notation;
    }

    /**
     * @return array<string>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }
}

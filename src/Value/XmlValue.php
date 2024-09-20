<?php

declare(strict_types=1);

namespace Zodimo\Xml\Value;

use Zodimo\BaseReturn\Option;

class XmlValue
{
    private string $name;

    /**
     * @var array<string,mixed>
     */
    private array $attributes;

    /**
     * @var Option<mixed>
     */
    private Option $value;

    /**
     * @var array<string,array<XmlValue>|XmlValue>
     */
    private array $children;

    /**
     * @var array<string>
     */
    private array $cdata;

    /**
     * @param array<string,mixed>                    $attributes
     * @param Option<mixed>                          $value
     * @param array<string,array<XmlValue>|XmlValue> $children
     * @param array<string>                          $cdata
     */
    private function __construct(string $name, array $attributes, Option $value, array $children, array $cdata)
    {
        $this->name = $name;
        $this->attributes = $attributes;
        $this->value = $value;
        $this->children = $children;
        $this->cdata = $cdata;
    }

    /**
     * @param array<string,mixed>                    $attributes
     * @param Option<mixed>                          $value
     * @param array<string,array<XmlValue>|XmlValue> $children
     * @param array<string>                          $cdata
     */
    public static function create(string $name, array $attributes, Option $value, array $children, array $cdata): self
    {
        return new self($name, $attributes, $value, $children, $cdata);
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array<string,mixed>
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @return Option<mixed>
     */
    public function getValue(): Option
    {
        return $this->value;
    }

    /**
     * @return array<string,array<XmlValue>|XmlValue>
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * @return array<string>
     */
    public function getCData(): array
    {
        return $this->cdata;
    }
}

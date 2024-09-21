<?php

declare(strict_types=1);

namespace Zodimo\Xml\Value;

use Zodimo\BaseReturn\Option;

class XmlValueBuilder
{
    private string $name;

    /**
     * @var array<string,mixed>
     */
    private array $attributes;

    /**
     * @var array<string>
     */
    private array $values;

    /**
     * @var array<string,array<XmlValueBuilder>>
     */
    private array $children;

    /**
     * @var array<string>
     */
    private array $cdata;

    /**
     * @param array<string,mixed> $attributes
     */
    private function __construct(string $name, array $attributes)
    {
        $this->name = $name;
        $this->attributes = $attributes;
        $this->values = [];
        $this->children = [];
        $this->cdata = [];
    }

    /**
     * @param array<string,mixed> $attributes
     */
    public static function create(string $name, array $attributes): XmlValueBuilder
    {
        return new self($name, $attributes);
    }

    public function addValue(string $value): XmlValueBuilder
    {
        $this->values = [...$this->values, $value];

        return $this;
    }

    public function addChild(XmlValueBuilder $child): XmlValueBuilder
    {
        $this->children[$child->getName()][] = $child;

        return $this;
    }

    public function addCdata(string $data): XmlValueBuilder
    {
        $this->cdata[] = $data;

        return $this;
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
     * @return array<string>
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * @return array<string,array<XmlValueBuilder>>
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * @return array<string>
     */
    public function getCdata(): array
    {
        return $this->cdata;
    }

    public function build(): XmlValue
    {
        // build chidren
        $children = [];
        foreach ($this->getChildren() as $childName => $child) {
            $children[$childName] = array_map(fn (XmlValueBuilder $childBuilder) => $childBuilder->build(), $child);
        }

        $values = $this->getValues();
        if (count($values) > 0) {
            $value = Option::some(implode('', $values));
        } else {
            $value = Option::none();
        }

        return XmlValue::create(
            $this->getName(),
            $this->getAttributes(),
            $value,
            $children,
            $this->getCdata()
        );
    }
}

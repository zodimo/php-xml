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
     * @var Option<mixed>
     */
    private Option $value;

    /**
     * @var array<string,array<XmlValueBuilder>|XmlValueBuilder>
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
        $this->value = Option::none();
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

    /**
     * @param mixed $value
     */
    public function addValue($value): void
    {
        $this->value = Option::some($value);
    }

    public function addChild(XmlValueBuilder $child): void
    {
        $childName = $child->getName();
        if (key_exists($childName, $this->children)) {
            // convert to array
            $prevValue = $this->children[$childName];
            if (is_array($prevValue)) {
                $this->children[$childName] = [...$prevValue, $child];
            } else {
                $this->children[$childName] = [$prevValue, $child];
            }
        } else {
            $this->children[$childName] = $child;
        }
    }

    public function addCdata(string $data): void
    {
        $this->cdata[] = $data;
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
     * @return array<string,array<XmlValueBuilder>|XmlValueBuilder>
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
            if (is_array($child)) {
                $children[$childName] = array_map(fn (XmlValueBuilder $childBuilder) => $childBuilder->build(), $child);
            } else {
                $children[$childName] = $child->build();
            }
        }

        return XmlValue::create(
            $this->getName(),
            $this->getAttributes(),
            $this->getValue(),
            $children,
            $this->getCdata()
        );
    }
}

<?php

declare(strict_types=1);

namespace Zodimo\Xml\Tests\Unit\Value;

use PHPUnit\Framework\TestCase;
use Zodimo\BaseReturn\Option;
use Zodimo\Xml\Value\XmlValue;

/**
 * @internal
 *
 * @coversNothing
 */
class XmlValueTest extends TestCase
{
    public function testCanCreateWithEmptyAttributes(): void
    {
        $xmlValue = XmlValue::create(
            'root',
            [],
            Option::none(),
            [],
            []
        );
        $this->assertInstanceOf(XmlValue::class, $xmlValue);
        $this->assertEquals('root', $xmlValue->getName(), 'name');
        $this->assertEquals([], $xmlValue->getAttributes(), 'attributes');
        $this->assertEquals([], $xmlValue->getCdata(), 'cdata');
        $this->assertEquals([], $xmlValue->getChildren(), 'children');
        $this->assertEquals(Option::none(), $xmlValue->getValue(), 'value');
    }

    public function testCanCreateWithAttributes(): void
    {
        $xmlValue = XmlValue::create(
            'root',
            ['name' => 'Joe'],
            Option::none(),
            [],
            []
        );
        $this->assertEquals('root', $xmlValue->getName(), 'name');
        $this->assertEquals(['name' => 'Joe'], $xmlValue->getAttributes(), 'attributes');
        $this->assertEquals([], $xmlValue->getCdata(), 'cdata');
        $this->assertEquals([], $xmlValue->getChildren(), 'children');
        $this->assertEquals(Option::none(), $xmlValue->getValue(), 'value');
    }

    public function testCanAddValue(): void
    {
        $xmlValue = XmlValue::create(
            'root',
            ['name' => 'Joe'],
            Option::some('some-value'),
            [],
            []
        );

        $this->assertEquals('root', $xmlValue->getName(), 'name');
        $this->assertEquals(['name' => 'Joe'], $xmlValue->getAttributes(), 'attributes');
        $this->assertEquals([], $xmlValue->getCdata(), 'cdata');
        $this->assertEquals([], $xmlValue->getChildren(), 'children');
        $this->assertEquals(Option::some('some-value'), $xmlValue->getValue(), 'value');
    }

    public function testCanAddCdata(): void
    {
        $xmlValue = XmlValue::create(
            'root',
            ['name' => 'Joe'],
            Option::none(),
            [],
            ['some-cdata']
        );

        $this->assertEquals('root', $xmlValue->getName(), 'name');
        $this->assertEquals(['name' => 'Joe'], $xmlValue->getAttributes(), 'attributes');
        $this->assertEquals(['some-cdata'], $xmlValue->getCdata(), 'cdata');
        $this->assertEquals([], $xmlValue->getChildren(), 'children');
        $this->assertEquals(Option::none(), $xmlValue->getValue(), 'value');
    }

    public function testCanAddChild(): void
    {
        $child = XmlValue::create(
            'user',
            [],
            Option::none(),
            [],
            []
        );

        $xmlValue = XmlValue::create(
            'root',
            ['name' => 'Joe'],
            Option::none(),
            [
                $child->getName() => [$child],
            ],
            []
        );

        $this->assertEquals('root', $xmlValue->getName(), 'name');
        $this->assertEquals(['name' => 'Joe'], $xmlValue->getAttributes(), 'attributes');
        $this->assertEquals([], $xmlValue->getCdata(), 'cdata');
        $this->assertEquals(['user' => [$child]], $xmlValue->getChildren(), 'children');
        $this->assertEquals(Option::none(), $xmlValue->getValue(), 'value');
    }

    public function testCanAddChildren(): void
    {
        $child1 = XmlValue::create(
            'user',
            [],
            Option::none(),
            [],
            []
        );
        $child2 = XmlValue::create(
            'user',
            [],
            Option::none(),
            [],
            []
        );
        $xmlValue = XmlValue::create(
            'root',
            ['name' => 'Joe'],
            Option::none(),
            [$child1->getName() => [
                $child1,
                $child2,
            ]],
            []
        );

        $this->assertEquals('root', $xmlValue->getName(), 'name');
        $this->assertEquals(['name' => 'Joe'], $xmlValue->getAttributes(), 'attributes');
        $this->assertEquals([], $xmlValue->getCdata(), 'cdata');
        $this->assertEquals(['user' => [$child1, $child2]], $xmlValue->getChildren(), 'children');
        $this->assertEquals(Option::none(), $xmlValue->getValue(), 'value');
    }
}

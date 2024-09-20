<?php

declare(strict_types=1);

namespace Zodimo\Xml\Tests\Unit\Value;

use PHPUnit\Framework\TestCase;
use Zodimo\BaseReturn\Option;
use Zodimo\Xml\Value\XmlValueBuilder;

/**
 * @internal
 *
 * @coversNothing
 */
class XmlValueBuilderTest extends TestCase
{
    public function testCanCreateWithEmptyAttributes(): void
    {
        $builder = XmlValueBuilder::create('root', []);
        $this->assertInstanceOf(XmlValueBuilder::class, $builder);
        $this->assertEquals('root', $builder->getName(), 'name');
        $this->assertEquals([], $builder->getAttributes(), 'attributes');
        $this->assertEquals([], $builder->getCdata(), 'cdata');
        $this->assertEquals([], $builder->getChildren(), 'children');
        $this->assertEquals(Option::none(), $builder->getValue(), 'value');
    }

    public function testCanCreateWithAttributes(): void
    {
        $builder = XmlValueBuilder::create('root', ['name' => 'Joe']);
        $this->assertEquals('root', $builder->getName(), 'name');
        $this->assertEquals(['name' => 'Joe'], $builder->getAttributes(), 'attributes');
        $this->assertEquals([], $builder->getCdata(), 'cdata');
        $this->assertEquals([], $builder->getChildren(), 'children');
        $this->assertEquals(Option::none(), $builder->getValue(), 'value');
    }

    public function testCanAddValue(): void
    {
        $builder = XmlValueBuilder::create('root', ['name' => 'Joe']);
        $builder->addValue('some-value');
        $this->assertEquals('root', $builder->getName(), 'name');
        $this->assertEquals(['name' => 'Joe'], $builder->getAttributes(), 'attributes');
        $this->assertEquals([], $builder->getCdata(), 'cdata');
        $this->assertEquals([], $builder->getChildren(), 'children');
        $this->assertEquals(Option::some('some-value'), $builder->getValue(), 'value');
    }

    public function testCanAddCdata(): void
    {
        $builder = XmlValueBuilder::create('root', ['name' => 'Joe']);
        $builder->addCdata('some-cdata');
        $this->assertEquals('root', $builder->getName(), 'name');
        $this->assertEquals(['name' => 'Joe'], $builder->getAttributes(), 'attributes');
        $this->assertEquals(['some-cdata'], $builder->getCdata(), 'cdata');
        $this->assertEquals([], $builder->getChildren(), 'children');
        $this->assertEquals(Option::none(), $builder->getValue(), 'value');
    }

    public function testCanAddChild(): void
    {
        $builder = XmlValueBuilder::create('root', ['name' => 'Joe']);
        $child = XmlValueBuilder::create('user', []);
        $builder->addChild($child);
        $this->assertEquals('root', $builder->getName(), 'name');
        $this->assertEquals(['name' => 'Joe'], $builder->getAttributes(), 'attributes');
        $this->assertEquals([], $builder->getCdata(), 'cdata');
        $this->assertEquals(['user' => $child], $builder->getChildren(), 'children');
        $this->assertEquals(Option::none(), $builder->getValue(), 'value');
    }

    public function testCanAddChildren(): void
    {
        $builder = XmlValueBuilder::create('root', ['name' => 'Joe']);
        $child1 = XmlValueBuilder::create('user', []);
        $child2 = XmlValueBuilder::create('user', []);
        $builder->addChild($child1);
        $builder->addChild($child2);
        $this->assertEquals('root', $builder->getName(), 'name');
        $this->assertEquals(['name' => 'Joe'], $builder->getAttributes(), 'attributes');
        $this->assertEquals([], $builder->getCdata(), 'cdata');
        $this->assertEquals(['user' => [$child1, $child2]], $builder->getChildren(), 'children');
        $this->assertEquals(Option::none(), $builder->getValue(), 'value');
    }
}

<?php

declare(strict_types=1);

namespace Zodimo\Xml\Tests\Integration\Value;

use PHPUnit\Framework\TestCase;
use Zodimo\BaseReturn\Option;
use Zodimo\Xml\Value\XmlValue;
use Zodimo\Xml\Value\XmlValueBuilder;

/**
 * @internal
 *
 * @coversNothing
 */
class XmlValueBuilderTest extends TestCase
{
    public function testCanBuildWithEmptyAttributes(): void
    {
        $builder = XmlValueBuilder::create('root', []);

        $xmlValueFromBuilder = $builder->build();

        $expectedXmlValue = XmlValue::create(
            'root',
            [],
            Option::none(),
            [],
            []
        );
        $this->assertEquals($expectedXmlValue, $xmlValueFromBuilder);
    }

    public function testCanCreateWithAttributes(): void
    {
        $builder = XmlValueBuilder::create('root', ['name' => 'Joe']);
        $xmlValueFromBuilder = $builder->build();
        $expectedXmlValue = XmlValue::create(
            'root',
            ['name' => 'Joe'],
            Option::none(),
            [],
            []
        );

        $this->assertEquals($expectedXmlValue, $xmlValueFromBuilder);
    }

    public function testCanAddValue(): void
    {
        $builder = XmlValueBuilder::create('root', ['name' => 'Joe']);
        $builder->addValue('some-value');
        $xmlValueFromBuilder = $builder->build();
        $expectedXmlValue = XmlValue::create(
            'root',
            ['name' => 'Joe'],
            Option::some('some-value'),
            [],
            []
        );
        $this->assertEquals($expectedXmlValue, $xmlValueFromBuilder);
    }

    public function testCanAddCdata(): void
    {
        $builder = XmlValueBuilder::create('root', ['name' => 'Joe']);
        $builder->addCdata('some-cdata');
        $xmlValueFromBuilder = $builder->build();
        $expectedXmlValue = XmlValue::create(
            'root',
            ['name' => 'Joe'],
            Option::none(),
            [],
            ['some-cdata']
        );
        $this->assertEquals($expectedXmlValue, $xmlValueFromBuilder);
    }

    public function testCanAddChild(): void
    {
        $builder = XmlValueBuilder::create('root', ['name' => 'Joe']);
        $child = XmlValueBuilder::create('user', []);
        $builder->addChild($child);
        $xmlValueFromBuilder = $builder->build();
        $expectedChild = XmlValue::create(
            'user',
            [],
            Option::none(),
            [],
            []
        );

        $expectedXmlValue = XmlValue::create(
            'root',
            ['name' => 'Joe'],
            Option::none(),
            [
                $child->getName() => [$expectedChild],
            ],
            []
        );
        $this->assertEquals($expectedXmlValue, $xmlValueFromBuilder);
    }

    public function testCanAddChildren(): void
    {
        $builder = XmlValueBuilder::create('root', ['name' => 'Joe']);
        $child1 = XmlValueBuilder::create('user', []);
        $child2 = XmlValueBuilder::create('user', []);
        $builder->addChild($child1);
        $builder->addChild($child2);
        $xmlValueFromBuilder = $builder->build();

        $expectedChild1 = XmlValue::create(
            'user',
            [],
            Option::none(),
            [],
            []
        );
        $expectedChild2 = XmlValue::create(
            'user',
            [],
            Option::none(),
            [],
            []
        );
        $expectedXmlValue = XmlValue::create(
            'root',
            ['name' => 'Joe'],
            Option::none(),
            [$expectedChild1->getName() => [
                $expectedChild1,
                $expectedChild2,
            ]],
            []
        );

        $this->assertEquals($expectedXmlValue, $xmlValueFromBuilder);
    }
}

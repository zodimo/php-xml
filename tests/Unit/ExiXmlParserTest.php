<?php

declare(strict_types=1);

namespace Zodimo\Xml\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Zodimo\BaseReturnTest\MockClosureTrait;
use Zodimo\Xml\EXI\ExiEvent;
use Zodimo\Xml\ExiXmlParser;

/**
 * @internal
 *
 * @coversNothing
 */
class ExiXmlParserTest extends TestCase
{
    use MockClosureTrait;

    public function testCanCreate(): void
    {
        $parserResult = ExiXmlParser::create();
        $this->assertTrue($parserResult->isSuccess());
        $parser = $parserResult->unwrapSuccess($this->createClosureNotCalled());
        $this->assertInstanceOf(ExiXmlParser::class, $parser);
    }

    public function testCanParseString1(): void
    {
        $xmlstring = '<root/>';
        $parserResult = ExiXmlParser::create();

        $expectedEvents = [
            ExiEvent::startElement('root'),
            ExiEvent::endElement(),
        ];

        $collectedEvents = [];
        $callback = function (ExiEvent $event) use (&$collectedEvents) {
            $collectedEvents[] = $event;
        };

        $this->assertTrue($parserResult->isSuccess());
        $parser = $parserResult->unwrapSuccess($this->createClosureNotCalled());
        $parser->registerCallback('/', $callback);
        $result = $parser->parseString($xmlstring, true);
        $this->assertTrue($result->isSuccess());

        $this->assertEquals($expectedEvents, $collectedEvents);
    }
}

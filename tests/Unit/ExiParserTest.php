<?php

declare(strict_types=1);

namespace Zodimo\Xml\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Zodimo\BaseReturnTest\MockClosureTrait;
use Zodimo\Xml\ExiParser;

/**
 * @internal
 *
 * @coversNothing
 */
class ExiParserTest extends TestCase
{
    use MockClosureTrait;

    public function testCanCreate(): void
    {
        $parserResult = ExiParser::create();
        $this->assertTrue($parserResult->isSuccess());
        $parser = $parserResult->unwrapSuccess($this->createClosureNotCalled());
        $this->assertInstanceOf(ExiParser::class, $parser);
    }

    public function testCanParseString1(): void
    {
        $xmlstring = '<root/>';
        $parserResult = ExiParser::create();

        $expectedEvents = [];

        $collectedEvents = [];
        $callback = function (array $events) use (&$collectedEvents) {
            $collectedEvents = $events;
        };

        $this->assertTrue($parserResult->isSuccess());
        $parser = $parserResult->unwrapSuccess($this->createClosureNotCalled());
        $parser->registerCallback('/', $callback);
        $result = $parser->parseString($xmlstring, true);
        $this->assertTrue($result->isSuccess());

        $this->assertEquals($expectedEvents, $collectedEvents);
    }
}

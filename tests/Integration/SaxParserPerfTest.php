<?php

declare(strict_types=1);

namespace Zodimo\Xml\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Zodimo\BaseReturnTest\MockClosureTrait;
use Zodimo\Xml\SaxParser;

/**
 * @internal
 *
 * @coversNothing
 */
class SaxParserPerfTest extends TestCase
{
    use MockClosureTrait;

    public function testCanCreate(): void
    {
        $parserResult = SaxParser::create();
        $this->assertTrue($parserResult->isSuccess());
        $parser = $parserResult->unwrapSuccess($this->createClosureNotCalled());
        $this->assertInstanceOf(SaxParser::class, $parser);
    }

    public function testCanParseFile100k(): void
    {
        $counter = 0;
        $counterCallback = function ($data) use (&$counter) {
            ++$counter;
        };
        $filePath = __DIR__.'/../Resources/100k.xml.gz';
        $parserResult = SaxParser::create();
        $this->assertTrue($parserResult->isSuccess());
        $parser = $parserResult->unwrapSuccess($this->createClosureNotCalled());
        $parser->registerCallback('/root/user', $counterCallback);
        $result = $parser->parseFile($filePath);
        $this->assertTrue($result->isSuccess());
        $this->assertEquals(100000, $counter);
    }

    public function testCanParseFile1k(): void
    {
        $counter = 0;
        $counterCallback = function ($data) use (&$counter) {
            ++$counter;
        };
        $filePath = __DIR__.'/../Resources/1k.xml.gz';
        $parserResult = SaxParser::create();
        $this->assertTrue($parserResult->isSuccess());
        $parser = $parserResult->unwrapSuccess($this->createClosureNotCalled());
        $parser->registerCallback('/root/user', $counterCallback);
        $result = $parser->parseFile($filePath);
        $this->assertTrue($result->isSuccess());
        $this->assertEquals(1000, $counter);
    }
}

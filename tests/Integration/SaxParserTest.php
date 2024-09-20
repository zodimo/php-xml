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
class SaxParserTest extends TestCase
{
    use MockClosureTrait;

    public function testCanCreate(): void
    {
        $parserResult = SaxParser::create();
        $this->assertTrue($parserResult->isSuccess());
        $parser = $parserResult->unwrapSuccess($this->createClosureNotCalled());
        $this->assertInstanceOf(SaxParser::class, $parser);
    }

    public function testCanParseFile1k(): void
    {
        $callback = $this->createClosureMock();
        $callback->expects($this->exactly(1000))->method('__invoke');
        $filePath = __DIR__.'/../Resources/1k.xml';
        $parserResult = SaxParser::create();
        $this->assertTrue($parserResult->isSuccess());
        $parser = $parserResult->unwrapSuccess($this->createClosureNotCalled());
        $parser->registerCallback('/root/user', $callback);
        $result = $parser->parseFile($filePath);
        $this->assertTrue($result->isSuccess());
    }

    public function testCanParseFile1kGzipped(): void
    {
        $callback = $this->createClosureMock();
        $callback->expects($this->exactly(1000))->method('__invoke');
        $filePath = __DIR__.'/../Resources/1k.xml.gz';
        $parserResult = SaxParser::create();
        $this->assertTrue($parserResult->isSuccess());
        $parser = $parserResult->unwrapSuccess($this->createClosureNotCalled());
        $parser->registerCallback('/root/user', $callback);
        $result = $parser->parseFile($filePath);
        $this->assertTrue($result->isSuccess());
    }
}

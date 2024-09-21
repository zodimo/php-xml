<?php

declare(strict_types=1);

namespace Zodimo\Xml\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Zodimo\BaseReturnTest\MockClosureTrait;
use Zodimo\Xml\ExiParser;
use Zodimo\Xml\NoOpParser;

/**
 * @internal
 *
 * @coversNothing
 */
class NoOpParserPerfTest extends TestCase
{
    use MockClosureTrait;

    public function testCanCreate(): void
    {
        $parserResult = NoOpParser::create();
        $this->assertTrue($parserResult->isSuccess());
        $parser = $parserResult->unwrapSuccess($this->createClosureNotCalled());
        $this->assertInstanceOf(NoOpParser::class, $parser);
    }

    public function testCanParseFile100k(): void
    {
        $filePath = __DIR__.'/../Resources/100k.xml.gz';
        $parserResult = NoOpParser::create();
        $this->assertTrue($parserResult->isSuccess());
        $parser = $parserResult->unwrapSuccess($this->createClosureNotCalled());
        $result = $parser->parseFile($filePath);
        $this->assertTrue($result->isSuccess());
    }

    // public function testCanParseFile1m(): void
    // {
    //     $filePath = __DIR__.'/../Resources/1m.xml.gz';
    //     $parserResult = NoOpParser::create();
    //     $this->assertTrue($parserResult->isSuccess());
    //     $parser = $parserResult->unwrapSuccess($this->createClosureNotCalled());
    //     $result = $parser->parseFile($filePath);
    //     $this->assertTrue($result->isSuccess());
    // }

    // public function testCanParseFile1k(): void
    // {
    //     $callback = $this->createClosureMock();
    //     $callback->expects($this->exactly(1000))->method('__invoke');
    //     $filePath = __DIR__.'/../Resources/1k.xml.gz';
    //     $parserResult = ExiParser::create();
    //     $this->assertTrue($parserResult->isSuccess());
    //     $parser = $parserResult->unwrapSuccess($this->createClosureNotCalled());
    //     $parser->registerCallback('/root/user', $callback);
    //     $result = $parser->parseFile($filePath);
    //     $this->assertTrue($result->isSuccess());
    // }
}

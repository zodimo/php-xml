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
        $parser = SaxParser::create();
        $this->assertInstanceOf(SaxParser::class, $parser);
    }

    public function testCanParseFile100k(): void
    {
        $callback = $this->createClosureMock();
        $callback->expects($this->exactly(100000))->method('__invoke');
        $filePath = __DIR__.'/../Resources/100k.xml.gz';
        $parser = SaxParser::create();
        $parser->registerCallback('/root/user', $callback);
        $result = $parser->parseFile($filePath);
        $this->assertTrue($result->isSuccess());
    }

    public function testCanParseFile1k(): void
    {
        $callback = $this->createClosureMock();
        $callback->expects($this->exactly(1000))->method('__invoke');
        $filePath = __DIR__.'/../Resources/1k.xml.gz';
        $parser = SaxParser::create();
        $parser->registerCallback('/root/user', $callback);
        $result = $parser->parseFile($filePath);
        $this->assertTrue($result->isSuccess());
    }
}

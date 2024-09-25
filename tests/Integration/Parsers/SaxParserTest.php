<?php

declare(strict_types=1);

namespace Zodimo\Xml\Tests\Integration\Parsers;

use PHPUnit\Framework\TestCase;
use Zodimo\BaseReturnTest\MockClosureTrait;
use Zodimo\Xml\Parsers\SaxParser;

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
        $counter = 0;
        $counterCallback = function ($data) use (&$counter) {
            ++$counter;
        };
        $filePath = __DIR__.'/../Resources/1k.xml';
        $parserResult = SaxParser::create()
            ->flatMap(fn ($p) => $p->registerCallback('/root/user', $counterCallback))
        ;
        $this->assertTrue($parserResult->isSuccess());
        $resultTuple = $parserResult->unwrapSuccess($this->createClosureNotCalled());
        $result = $resultTuple->snd()->parseFile($filePath);
        $this->assertTrue($result->isSuccess());
        $this->assertEquals(1000, $counter);
    }

    public function testCanParseFile1kGzipped(): void
    {
        $counter = 0;
        $counterCallback = function ($data) use (&$counter) {
            ++$counter;
        };
        $filePath = __DIR__.'/../Resources/1k.xml.gz';
        $parserResult = SaxParser::create()
            ->flatMap(fn ($p) => $p->registerCallback('/root/user', $counterCallback))
        ;
        $this->assertTrue($parserResult->isSuccess());
        $resultTuple = $parserResult->unwrapSuccess($this->createClosureNotCalled());
        $result = $resultTuple->snd()->parseFile($filePath);
        $this->assertTrue($result->isSuccess());
        $this->assertEquals(1000, $counter);
    }
}

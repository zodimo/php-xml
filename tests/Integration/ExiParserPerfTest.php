<?php

declare(strict_types=1);

namespace Zodimo\Xml\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Zodimo\BaseReturnTest\MockClosureTrait;
use Zodimo\Xml\ExiParser;

/**
 * @internal
 *
 * @coversNothing
 */
class ExiParserPerfTest extends TestCase
{
    use MockClosureTrait;

    // public function testCanCreate(): void
    // {
    //     $parserResult = ExiParser::create();
    //     $this->assertTrue($parserResult->isSuccess());
    //     $parser = $parserResult->unwrapSuccess($this->createClosureNotCalled());
    //     $this->assertInstanceOf(ExiParser::class, $parser);
    // }

    public function testCanParseFile100k(): void
    {
        $counter = 0;
        $counterCallback = function ($data) use (&$counter) {
            ++$counter;
        };
        $filePath = __DIR__.'/../Resources/100k.xml.gz';
        $parserResult = ExiParser::create();
        $this->assertTrue($parserResult->isSuccess());
        $parser = $parserResult->unwrapSuccess($this->createClosureNotCalled());
        $parser->registerCallback('/root/user', $counterCallback);
        $result = $parser->parseFile($filePath);
        $this->assertTrue($result->isSuccess());
        $this->assertGreaterThan(100000, $counter);
    }

    // public function testCanParseFile10k(): void
    // {
    //     $counter = 0;
    //     $counterCallback = function ($data) use (&$counter) {
    //         ++$counter;
    //     };
    //     $filePath = __DIR__.'/../Resources/10k.xml.gz';
    //     $parserResult = ExiParser::create();
    //     $this->assertTrue($parserResult->isSuccess());
    //     $parser = $parserResult->unwrapSuccess($this->createClosureNotCalled());
    //     $parser->registerCallback('/root/user', $counterCallback);
    //     $result = $parser->parseFile($filePath);
    //     $this->assertTrue($result->isSuccess());
    //     $this->assertGreaterThan(10000, $counter);
    // }
}

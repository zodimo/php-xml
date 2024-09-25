<?php

declare(strict_types=1);

namespace Zodimo\Xml\Tests\Integration\Parsers;

use PHPUnit\Framework\TestCase;
use Zodimo\BaseReturnTest\MockClosureTrait;
use Zodimo\Xml\Parsers\SimpleXMLReader;

/**
 * @internal
 *
 * @coversNothing
 */
class SimpleXmlReaderTest extends TestCase
{
    use MockClosureTrait;

    // public function testParseFile100k(): void
    // {
    //     $counter = 0;
    //     $counterCallback = function ($data) use (&$counter) {
    //         ++$counter;

    //         return true;
    //     };
    //     $filePath = __DIR__.'/../Resources/100k.xml.gz';
    //     $parserResult = SimpleXMLReader::create()
    //         ->flatMap(fn ($parser) => $parser->registerCallback('/root/user', $counterCallback))
    //     ;
    //     $parserResult->flatMap(fn ($inputTuple) => $inputTuple->snd()->parseFile($filePath));
    //     $this->assertEquals(100000, $counter);
    // }

    public function testParseFile1m(): void
    {
        $counter = 0;
        $counterCallback = function ($parser) use (&$counter) {
            ++$counter;
            if ($parser instanceof SimpleXMLReader) {
                $name = $parser->readString();
            }

            return true;
        };
        $filePath = __DIR__.'/../Resources/1m.xml.gz';
        $parserResult = SimpleXMLReader::create()
            ->flatMap(fn ($parser) => $parser->registerCallback('/root/user/name', $counterCallback))
        ;
        $parserResult->flatMap(fn ($inputTuple) => $inputTuple->snd()->parseFile($filePath));
        $this->assertEquals(1000000, $counter);
    }
}

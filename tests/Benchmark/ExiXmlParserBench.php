<?php

declare(strict_types=1);

namespace Zodimo\Xml\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Zodimo\Xml\ExiXmlParser;

/**
 * @internal
 *
 * @coversNothing
 */
class ExiXmlParserBench extends TestCase
{
    public function benchParseFile1k(): void
    {
        $noopCallback = function ($data) {};
        $filePath = __DIR__.'/../Resources/1k.xml.gz';
        $parserResult = ExiXmlParser::create()
            ->flatMap(fn ($parser) => $parser->registerCallback('/root/user', $noopCallback))
        ;
        $parserResult->flatMap(fn ($inputTuple) => $inputTuple->snd()->parseFile($filePath));
    }

    public function benchParseFile10k(): void
    {
        $noopCallback = function ($data) {};
        $filePath = __DIR__.'/../Resources/10k.xml.gz';
        $parserResult = ExiXmlParser::create()
            ->flatMap(fn ($parser) => $parser->registerCallback('/root/user', $noopCallback))
        ;
        $parserResult->flatMap(fn ($inputTuple) => $inputTuple->snd()->parseFile($filePath));
    }
    // public function benchParseFile100k(): void
    // {
    //     $noopCallback = function ($data) {};
    //     $filePath = __DIR__.'/../Resources/100k.xml.gz';
    //     $parserResult = ExiParser::create()
    //         ->flatMap(fn ($parser) => $parser->registerCallback('/root/user', $noopCallback))
    //     ;
    //     $parserResult->flatMap(fn ($inputTuple) => $inputTuple->snd()->parseFile($filePath));
    // }
}

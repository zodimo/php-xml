<?php

declare(strict_types=1);

namespace Zodimo\Xml\Tests\Benchmark;

use Zodimo\Xml\NoOpSaxParser;

/**
 * @internal
 *
 * @coversNothing
 */
class NoOpSaxParserBench
{
    public function benchParseFile1k(): void
    {
        $filePath = __DIR__.'/../Resources/1k.xml.gz';
        $parserResult = NoOpSaxParser::create();
        $parserResult->flatMap(fn ($parser) => $parser->parseFile($filePath));
    }

    public function benchParseFile10k(): void
    {
        $filePath = __DIR__.'/../Resources/10k.xml.gz';
        $parserResult = NoOpSaxParser::create();
        $parserResult->flatMap(fn ($parser) => $parser->parseFile($filePath));
    }

    public function benchParseFile100k(): void
    {
        $filePath = __DIR__.'/../Resources/100k.xml.gz';
        $parserResult = NoOpSaxParser::create();
        $parserResult->flatMap(fn ($parser) => $parser->parseFile($filePath));
    }
}

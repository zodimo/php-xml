<?php

declare(strict_types=1);

namespace Zodimo\Xml\Tests\Benchmark;

use Zodimo\Xml\Parsers\SimpleXMLReader;

/**
 * @internal
 *
 * @coversNothing
 */
class SimpleXMLReaderBench
{
    public function getCallback(): callable
    {
        return fn ($_) => true;
    }

    public function benchParseFile1k(): void
    {
        $filePath = __DIR__.'/../Resources/1k.xml.gz';
        $parserResult = SimpleXMLReader::create()
            ->flatMap(fn ($parser) => $parser->registerCallback('_', $this->getCallback()))
        ;
        $parserResult->flatMap(fn ($inputTuple) => $inputTuple->snd()->parseFile($filePath));
    }

    public function benchParseFile10k(): void
    {
        $filePath = __DIR__.'/../Resources/10k.xml.gz';
        $parserResult = SimpleXMLReader::create()
            ->flatMap(fn ($parser) => $parser->registerCallback('_', $this->getCallback()))
        ;
        $parserResult->flatMap(fn ($inputTuple) => $inputTuple->snd()->parseFile($filePath));
    }

    public function benchParseFile100k(): void
    {
        $filePath = __DIR__.'/../Resources/100k.xml.gz';
        $parserResult = SimpleXMLReader::create()
            ->flatMap(fn ($parser) => $parser->registerCallback('_', $this->getCallback()))
        ;
        $parserResult->flatMap(fn ($inputTuple) => $inputTuple->snd()->parseFile($filePath));
    }
}

<?php

declare(strict_types=1);

namespace Zodimo\Xml\Tests\Benchmark;

use Zodimo\Xml\NoOpSimpleXMLReader;

/**
 * @internal
 *
 * @coversNothing
 */
class NoOpSimpleXMLReaderBench
{
    public function benchParseFile1k(): void
    {
        $filePath = __DIR__.'/../Resources/1k.xml.gz';
        $parser = NoOpSimpleXMLReader::fromXmlFile($filePath);
        $parser->parse();
    }

    public function benchParseFile10k(): void
    {
        $filePath = __DIR__.'/../Resources/10k.xml.gz';
        $parser = NoOpSimpleXMLReader::fromXmlFile($filePath);
        $parser->parse();
    }

    public function benchParseFile100k(): void
    {
        $filePath = __DIR__.'/../Resources/100k.xml.gz';
        $parser = NoOpSimpleXMLReader::fromXmlFile($filePath);
        $parser->parse();
    }
}

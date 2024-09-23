<?php

declare(strict_types=1);

namespace Zodimo\Xml\Tests\Benchmark;

use Zodimo\Xml\SimpleXMLReader;

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
        $parser = SimpleXMLReader::fromXmlFile($filePath);
        $parser = $parser->registerCallback('_', $this->getCallback());
        $parser->parse();
    }

    public function benchParseFile10k(): void
    {
        $filePath = __DIR__.'/../Resources/10k.xml.gz';
        $parser = SimpleXMLReader::fromXmlFile($filePath);
        $parser = $parser->registerCallback('_', $this->getCallback());
        $parser->parse();
    }

    public function benchParseFile100k(): void
    {
        $filePath = __DIR__.'/../Resources/100k.xml.gz';
        $parser = SimpleXMLReader::fromXmlFile($filePath);
        $parser = $parser->registerCallback('_', $this->getCallback());
        $parser->parse();
    }
}

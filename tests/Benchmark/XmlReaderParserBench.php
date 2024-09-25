<?php

declare(strict_types=1);

namespace Zodimo\Xml\Tests\Benchmark;

use Zodimo\BaseReturn\IOMonad;
use Zodimo\Xml\Errors\XmlParserException;
use Zodimo\Xml\Errors\XmlParsingException;
use Zodimo\Xml\SimpleXmlReaderInterface;
use Zodimo\Xml\XmlReaderParser;

class XmlReaderParserBench
{
    public function getResourcePath(string $resourceFileName): string
    {
        return __DIR__.'/../Resources/'.$resourceFileName;
    }

    /**
     * @return IOMonad<void,XmlParserException|XmlParsingException>
     */
    public function evaluateXpath(string $resourceFileName, string $xpathExpression): IOMonad
    {
        $filePath = $this->getResourcePath($resourceFileName);

        $cbCounter = function (SimpleXmlReaderInterface $xml) {
            return true;
        };

        $xpathParser = XmlReaderParser::create()->registerCallback($xpathExpression, $cbCounter);

        // @phpstan-ignore return.type
        return $xpathParser->flatMap(fn ($parser) => $parser->parseGzipFile($filePath));
    }

    public function bench10k(): void
    {
        $resourceFileName = '10k.xml.gz';
        $result = $this->evaluateXpath($resourceFileName, '/root/user');
    }

    public function bench100k(): void
    {
        $resourceFileName = '100k.xml.gz';
        $result = $this->evaluateXpath($resourceFileName, '/root/user');
    }
}

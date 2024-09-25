<?php

declare(strict_types=1);

namespace Zodimo\Xml\Tests\Integration;

use SimpleXMLElement;
use Zodimo\BaseReturn\IOMonad;
use Zodimo\Xml\Errors\XmlParserException;
use Zodimo\Xml\Errors\XmlParsingException;
use Zodimo\Xml\XPathParser;

class XPathParserBench
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

        $cbCounter = function (SimpleXMLElement $xml) {
            return true;
        };

        $xpathParser = XPathParser::create($xpathExpression, $cbCounter);

        return $xpathParser->parseGzipFile($filePath);
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

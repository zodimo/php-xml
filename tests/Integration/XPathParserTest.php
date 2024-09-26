<?php

declare(strict_types=1);

namespace Zodimo\Xml\Tests\Integration;

use PHPUnit\Framework\TestCase;
use SimpleXMLElement;
use Zodimo\BaseReturn\IOMonad;
use Zodimo\BaseReturnTest\MockClosureTrait;
use Zodimo\Xml\Errors\XmlParserException;
use Zodimo\Xml\Errors\XmlParsingException;
use Zodimo\Xml\XPathParser;

/**
 * @internal
 *
 * @coversNothing
 */
class XPathParserTest extends TestCase
{
    use MockClosureTrait;

    public function getResourcePath(string $resourceFileName): string
    {
        return __DIR__.'/../Resources/'.$resourceFileName;
    }

    /**
     * @return IOMonad<void,XmlParserException|XmlParsingException>
     */
    public function evaluateXpath(string $resourceFileName, string $xpathExpression, callable $cb): IOMonad
    {
        $filePath = $this->getResourcePath($resourceFileName);

        $xpathParser = XPathParser::create($xpathExpression, $cb);

        return $xpathParser->parseGzipFile($filePath);
    }

    public function test10k(): void
    {
        $resourceFileName = '10k.xml.gz';
        $counter = 0;
        $cbCounter = function (SimpleXMLElement $xml) use (&$counter) {
            ++$counter;

            return true;
        };

        $result = $this->evaluateXpath($resourceFileName, '/root/user', $cbCounter);
        $this->assertTrue($result->isSuccess());
        $this->assertEquals(10000, $counter);
    }

    public function testCanParseXmlString(): void
    {
        $xmlString = <<<'XML'
            <?xml version="1.0"?>
            <root>
            <user>
                <name>name1</name>
                <age>1</age>
            </user>
            <user>
                <name>name2</name>
                <age>2</age>
            </user>
            </root>
            XML;

        $callback = $this->createClosureMock();
        $callback->expects($this->exactly(2))->method('__invoke')->with($this->isInstanceOf(SimpleXMLElement::class))->willReturn(true);
        $xpathParser = XPathParser::create('/root/user', $callback);

        $result = $xpathParser->parseString($xmlString);
        $this->assertTrue($result->isSuccess());
    }
}

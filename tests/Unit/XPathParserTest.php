<?php

declare(strict_types=1);

namespace Zodimo\Xml\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SimpleXMLElement;
use Zodimo\BaseReturnTest\MockClosureTrait;
use Zodimo\Xml\XPathParser;

/**
 * @internal
 *
 * @coversNothing
 */
class XPathParserTest extends TestCase
{
    use MockClosureTrait;

    public function testCanCreate(): void
    {
        $xpath = '/';
        $parser = XPathParser::create($xpath, $this->createClosureMock());

        $this->assertInstanceOf(XPathParser::class, $parser);
    }

    public function testCanCallParseXmlFile(): void
    {
        $filePath = __DIR__.'/../Resources/10.xml';

        $xpath = '/root/user';

        $mockCallback = $this->createClosureMock();
        $mockCallback->expects($this->exactly(10))->method('__invoke')->with($this->isInstanceOf(SimpleXMLElement::class))->willReturn(true);

        $parser = XPathParser::create($xpath, $mockCallback);

        $parseFileResult = $parser->parseFile($filePath);
        $this->assertTrue($parseFileResult->isSuccess());
    }

    public function testCanCallParseGzipFile(): void
    {
        $filePath = __DIR__.'/../Resources/10.xml.gz';

        $xpath = '/root/user';

        $mockCallback = $this->createClosureMock();
        $mockCallback->expects($this->exactly(10))->method('__invoke')->with($this->isInstanceOf(SimpleXMLElement::class))->willReturn(true);

        $parser = XPathParser::create($xpath, $mockCallback);

        $parseFileResult = $parser->parseGzipFile($filePath);
        $this->assertTrue($parseFileResult->isSuccess());
    }

    public function testCannotCallParseXmlFileWithGzippedFile(): void
    {
        $filePath = __DIR__.'/../Resources/10.xml.gz';

        $xpath = '/root/user';

        $mockCallback = $this->createClosureMock();
        $parser = XPathParser::create($xpath, $mockCallback);

        $parseFileResult = $parser->parseFile($filePath);
        $this->assertTrue($parseFileResult->isFailure());
    }
}

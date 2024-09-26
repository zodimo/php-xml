<?php

declare(strict_types=1);

namespace Zodimo\Xml\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Zodimo\BaseReturnTest\MockClosureTrait;
use Zodimo\Xml\XmlReaderParser;

/**
 * @internal
 *
 * @coversNothing
 */
class XmlReaderParserTest extends TestCase
{
    use MockClosureTrait;

    public function testCanCreate(): void
    {
        $parser = XmlReaderParser::create();

        $this->assertInstanceOf(XmlReaderParser::class, $parser);
    }

    public function testCanRegisterCallback(): void
    {
        $parser = XmlReaderParser::create();
        $parserResult = $parser->registerCallback('/', $this->createClosureMock());
        $this->assertTrue($parserResult->isSuccess());
    }

    public function testCanUnRegisterCallback(): void
    {
        $parser = XmlReaderParser::create();
        $registerResult = $parser->registerCallback('/', $this->createClosureMock());
        $unregisterResult = $registerResult->flatMap(fn ($parser) => $parser->unRegisterCallback('/'));
        $this->assertTrue($unregisterResult->isSuccess());
    }

    public function testCannotCallParseXmlFileWithoutCallback(): void
    {
        $filePath = __DIR__.'/../Resources/10.xml';
        $parser = XmlReaderParser::create();
        $parseFileResult = $parser->parseFile($filePath);
        $this->assertTrue($parseFileResult->isFailure());
    }

    public function testCanCallParseXmlFile(): void
    {
        $filePath = __DIR__.'/../Resources/10.xml';
        $parser = XmlReaderParser::create();
        $registerResult = $parser->registerCallback('/', $this->createClosureMock());

        $parseFileResult = $registerResult->flatMap(fn ($parser) => $parser->parseFile($filePath));
        $this->assertTrue($parseFileResult->isSuccess());
    }

    public function testCannotCallParseGzipFileWithoutCallback(): void
    {
        $filePath = __DIR__.'/../Resources/10.xml.gz';
        $parser = XmlReaderParser::create();
        $parseFileResult = $parser->parseGzipFile($filePath);
        $this->assertTrue($parseFileResult->isFailure());
    }

    public function testCanCallParseGzipFile(): void
    {
        $filePath = __DIR__.'/../Resources/10.xml.gz';
        $parser = XmlReaderParser::create();
        $registerResult = $parser->registerCallback('/root/user', $this->createClosureMock());

        $parseFileResult = $registerResult->flatMap(fn ($parser) => $parser->parseGzipFile($filePath));

        $this->assertTrue($parseFileResult->isSuccess());
    }

    public function testCannotCallParseXmlFileWithGzippedFile(): void
    {
        $filePath = __DIR__.'/../Resources/10.xml.gz';
        $parser = XmlReaderParser::create();
        $registerResult = $parser->registerCallback('/root/user', $this->createClosureMock());
        $parseFileResult = $registerResult->flatMap(fn ($parser) => $parser->parseFile($filePath));

        $this->assertTrue($parseFileResult->isFailure());
    }

    public function testCanCallParseXmlString(): void
    {
        $xmlString = '<root/>';
        $parser = XmlReaderParser::create();
        $registerResult = $parser->registerCallback('/', $this->createClosureMock());
        $parseFileResult = $registerResult->flatMap(fn ($parser) => $parser->parseString($xmlString));

        $this->assertTrue($parseFileResult->isSuccess());
    }
}

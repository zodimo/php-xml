<?php

declare(strict_types=1);

namespace Zodimo\Xml\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Zodimo\BaseReturn\Tuple;
use Zodimo\BaseReturnTest\MockClosureTrait;
use Zodimo\Xml\Parsers\SimpleXMLReader;
use Zodimo\Xml\Parsers\XmlParserInterface;

/**
 * @internal
 *
 * @coversNothing
 */
class SimpleXMLReaderTest extends TestCase
{
    use MockClosureTrait;

    public function testCanCreate(): void
    {
        $parserResult = SimpleXMLReader::create();
        $this->assertTrue($parserResult->isSuccess());
        $parser = $parserResult->unwrapSuccess($this->createClosureNotCalled());
        $this->assertInstanceOf(SimpleXMLReader::class, $parser);
    }

    public function testCanAttachCallback(): void
    {
        $closure = $this->createClosureMock();
        $parserResult = SimpleXMLReader::create()->flatMap(fn (XmlParserInterface $reader) => $reader->registerCallback('username', $closure));
        $this->assertTrue($parserResult->isSuccess());
    }

    public function testCanCallParseString(): void
    {
        $xmlstring = '<root/>';
        $parserResult = SimpleXMLReader::create()
            ->flatMap(fn ($p) => $p->registerCallback('/', $this->createClosureMock()))
        ;
        $this->assertTrue($parserResult->isSuccess());
        $resultTuple = $parserResult->unwrapSuccess($this->createClosureNotCalled());

        $result = $resultTuple->snd()->parseString($xmlstring, true);
        $this->assertTrue($result->isSuccess());
    }

    public function testCallbackIsCalled(): void
    {
        $xmlstring = <<< 'XML'
            <root>
                <username>joe</username>
            </root>
            XML;

        $callbackCounter = $this->createClosureMock();
        $callbackCounter->expects(self::once())
            ->method('__invoke')
        ;

        // @phpstan-ignore closure.unusedUse
        $callback = function ($data) use (&$collectedData, $callbackCounter) {
            $callbackCounter();

            return true;
        };
        $registrationResult = SimpleXMLReader::create()->flatMap(fn (XmlParserInterface $reader) => $reader->registerCallback('/root/username', $callback));

        $this->assertTrue($registrationResult->isSuccess());
        $registrationTuple = $registrationResult->unwrapSuccess($this->createClosureNotCalled());
        $readerResult = $registrationTuple->snd()->parseString($xmlstring, true);
        $this->assertTrue($readerResult->isSuccess());
    }

    public function testCallbackIsCalledTwice(): void
    {
        $xmlstring = <<< 'XML'
            <root>
                <username>joe</username>
                <username>joe</username>
            </root>
            XML;

        $callbackCounter = $this->createClosureMock();
        $callbackCounter->expects(self::exactly(2))
            ->method('__invoke')
        ;

        // @phpstan-ignore closure.unusedUse
        $callback = function (XmlParserInterface $parser) use (&$collectedData, $callbackCounter) {
            $callbackCounter();
            if ($parser instanceof SimpleXMLReader) {
                $name = $parser->localName;
            }

            return true;
        };
        $registrationResult = SimpleXMLReader::create()->flatMap(fn (XmlParserInterface $reader) => $reader->registerCallback('/root/username', $callback));

        $this->assertTrue($registrationResult->isSuccess());
        $registrationTuple = $registrationResult->unwrapSuccess($this->createClosureNotCalled());
        $readerResult = $registrationTuple->snd()->parseString($xmlstring, true);
        $this->assertTrue($readerResult->isSuccess());
    }

    public function testCanRemoveRegisteredCallback(): void
    {
        $closure = $this->createClosureMock();
        $registrationResult = SimpleXMLReader::create()->flatMap(fn (XmlParserInterface $reader) => $reader->registerCallback('username', $closure));

        $this->assertTrue($registrationResult->isSuccess());
        $registrationTuple = $registrationResult->unwrapSuccess($this->createClosureNotCalled());
        $this->assertInstanceOf(Tuple::class, $registrationTuple);
        $callbackRegistration = $registrationTuple->fst();
        $unregisterResult = $registrationTuple->snd()->unRegisterCallback($callbackRegistration);
        $this->assertTrue($unregisterResult->isSuccess());
    }

    public function testUnRegisteredCallbackIsNotCalled(): void
    {
        $xmlstring = <<< 'XML'
            <root>
                <username>joe</username>
            </root>
            XML;

        $callback = $this->createClosureNotCalled();
        $callback2 = $this->createClosureMock();
        $callback2->method('__invoke')->willReturn(true);

        $registrationResult = SimpleXMLReader::create()->flatMap(fn (XmlParserInterface $reader) => $reader->registerCallback('/root/username', $callback));

        $this->assertTrue($registrationResult->isSuccess());
        $registrationTuple = $registrationResult->unwrapSuccess($this->createClosureNotCalled());
        $parserWithRegisteredCallback = $registrationTuple->snd();
        $unregisterResult = $parserWithRegisteredCallback->unRegisterCallback($registrationTuple->fst())
            ->flatMap(fn ($parser) => $parser->registerCallback('/root/username', $callback2))
        ;
        $this->assertTrue($unregisterResult->isSuccess());
        $this->assertInstanceOf(Tuple::class, $unregisterResult->unwrapSuccess($this->createClosureNotCalled()));

        $readerResult = $unregisterResult->unwrapSuccess($this->createClosureNotCalled())->snd()->parseString($xmlstring, true);
        $this->assertTrue($readerResult->isSuccess());
    }

    public function testCanParseFile(): void
    {
        $counter = 0;
        $counterCallback = function ($data) use (&$counter) {
            ++$counter;

            return true;
        };
        $filePath = __DIR__.'/../Resources/10.xml';
        $parserResult = SimpleXMLReader::create()
            ->flatMap(fn ($p) => $p->registerCallback('/root/user', $counterCallback))
        ;
        $this->assertTrue($parserResult->isSuccess());
        $resultTuple = $parserResult->unwrapSuccess($this->createClosureNotCalled());
        $result = $resultTuple->snd()->parseFile($filePath);
        $this->assertTrue($result->isSuccess());
        $this->assertEquals(10, $counter);
    }

    public function testCanParseFileGzipped(): void
    {
        $counter = 0;
        $counterCallback = function ($data) use (&$counter) {
            ++$counter;

            return true;
        };
        $filePath = __DIR__.'/../Resources/10.xml.gz';

        $parserResult = SimpleXMLReader::create()
            ->flatMap(fn ($p) => $p->registerCallback('/root/user', $counterCallback))
        ;
        $this->assertTrue($parserResult->isSuccess());
        $resultTuple = $parserResult->unwrapSuccess($this->createClosureNotCalled());
        $result = $resultTuple->snd()->parseFile($filePath);
        $this->assertTrue($result->isSuccess());
        $this->assertEquals(10, $counter);
    }

    public function testCanParseXPath1(): void
    {
        $counter = 0;
        $counterCallback = function ($data) use (&$counter) {
            ++$counter;

            return true;
        };
        $filePath = __DIR__.'/../Resources/10.xml.gz';

        $parserResult = SimpleXMLReader::create()
            ->flatMap(fn ($p) => $p->registerCallback('/root/user[10]', $counterCallback))
        ;
        $this->assertTrue($parserResult->isSuccess());
        $resultTuple = $parserResult->unwrapSuccess($this->createClosureNotCalled());
        $result = $resultTuple->snd()->parseFile($filePath);
        $this->assertTrue($result->isSuccess());
        $this->assertEquals(10, $counter);
    }
}

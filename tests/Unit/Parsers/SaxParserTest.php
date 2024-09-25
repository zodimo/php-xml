<?php

declare(strict_types=1);

namespace Zodimo\Xml\Tests\Unit\Parsers;

use PHPUnit\Framework\TestCase;
use Zodimo\BaseReturn\IOMonad;
use Zodimo\BaseReturn\Tuple;
use Zodimo\BaseReturnTest\MockClosureTrait;
use Zodimo\Xml\Parsers\SaxParser;
use Zodimo\Xml\Parsers\XmlParserInterface;
use Zodimo\Xml\Registration\CallbackRegistration;
use Zodimo\Xml\Value\XmlValue;

/**
 * @internal
 *
 * @coversNothing
 */
class SaxParserTest extends TestCase
{
    use MockClosureTrait;

    public function testCanCreate(): void
    {
        $parserResult = SaxParser::create();
        $this->assertTrue($parserResult->isSuccess());
        $parser = $parserResult->unwrapSuccess($this->createClosureNotCalled());
        $this->assertInstanceOf(SaxParser::class, $parser);
    }

    public function testCanAttachCallback(): void
    {
        $closure = $this->createClosureMock();
        $parserResult = SaxParser::create()->flatMap(fn (XmlParserInterface $reader) => $reader->registerCallback('username', $closure));
        $this->assertTrue($parserResult->isSuccess());
    }

    public function testCanCallParseString(): void
    {
        $xmlstring = '<root/>';
        $parserResult = SaxParser::create()
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
        $callback = function (XmlValue $xmlValue) use (&$collectedData, $callbackCounter): IOMonad {
            $callbackCounter();

            return IOMonad::pure(null);
        };
        $registrationResult = SaxParser::create()->flatMap(fn (XmlParserInterface $reader) => $reader->registerCallback('/root/username', $callback));

        $this->assertTrue($registrationResult->isSuccess());
        $registrationTuple = $registrationResult->unwrapSuccess($this->createClosureNotCalled());
        $readerResult = $registrationTuple->snd()->parseString($xmlstring, true);
        $this->assertTrue($readerResult->isSuccess());
    }

    public function testCanParseString2(): void
    {
        $xmlstring = <<< 'XML'
            <root>
                <username>joe</username>
            </root>
            XML;

        $parserResult = SaxParser::create()
            ->flatMap(fn ($p) => $p->registerCallback('/root/username', $this->createClosureMock()))
        ;
        $this->assertTrue($parserResult->isSuccess());
        $resultTuple = $parserResult->unwrapSuccess($this->createClosureNotCalled());

        $result = $resultTuple->snd()->parseString($xmlstring, true);
        $this->assertTrue($result->isSuccess());
    }

    public function testAttachCallbackReturnCallbackRegistrationTuple(): void
    {
        $closure = $this->createClosureMock();
        $registrationResult = SaxParser::create()->flatMap(fn (XmlParserInterface $reader) => $reader->registerCallback('username', $closure));

        $this->assertTrue($registrationResult->isSuccess());
        $registrationTuple = $registrationResult->unwrapSuccess($this->createClosureNotCalled());
        $this->assertInstanceOf(Tuple::class, $registrationTuple);
        $this->assertInstanceOf(CallbackRegistration::class, $registrationTuple->fst());
        $this->assertInstanceOf(SaxParser::class, $registrationTuple->snd());
    }

    public function testCanRemoveRegisteredCallback(): void
    {
        $closure = $this->createClosureMock();
        $registrationResult = SaxParser::create()->flatMap(fn (XmlParserInterface $reader) => $reader->registerCallback('username', $closure));

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
        $callback2->method('__invoke')->willReturn(IOMonad::pure(null));

        $registrationResult = SaxParser::create()->flatMap(fn (XmlParserInterface $reader) => $reader->registerCallback('/root/username', $callback));

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

    // public function testCanHandleUserHandlerCallbackSuccess(): void
    // {
    //     $xmlstring = <<< 'XML'
    //         <root>
    //             <user>
    //               <username>joe_soap</username>
    //               <name>joe</name>
    //               <email>joe@mail.com</email>
    //               <address>
    //                 <line1>street</line1>
    //               </address>
    //             </user>
    //         </root>
    //         XML;

    //     $collectedData = [];
    //     $expectedData = [
    //         [
    //             'username' => 'joe_soap',
    //             'name' => 'joe',
    //             'email' => 'joe@mail.com',
    //             'address' => [
    //                 'line1' => 'street',
    //             ],
    //         ],
    //     ];

    //     $callback = function (XmlValue $xmlValue) use (&$collectedData): IOMonad {
    //         $collectedData[] = [
    //             'username' => $xmlValue->getChildren()['username'][0]->getValue()->unwrap(fn () => ''),
    //             'name' => $xmlValue->getChildren()['name'][0]->getValue()->unwrap(fn () => ''),
    //             'email' => $xmlValue->getChildren()['email'][0]->getValue()->unwrap(fn () => ''),
    //             'address' => $xmlValue->getChildren()['address'][0]->getValue()->unwrap(fn () => ''),
    //         ];

    //         return IOMonad::pure(null);
    //     };

    //     $registrationResult = SaxParser::create()->flatMap(fn (XmlParserInterface $reader) => $reader->registerCallback('/root/user', $callback));
    //     $this->assertTrue($registrationResult->isSuccess());
    //     $registrationTuple = $registrationResult->unwrapSuccess($this->createClosureNotCalled());
    //     $readerResult = $registrationTuple->snd()->parseString($xmlstring, true);
    //     $this->assertTrue($readerResult->isSuccess());
    //     $this->assertEquals($expectedData, $collectedData);
    // }
}

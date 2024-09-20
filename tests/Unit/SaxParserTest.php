<?php

declare(strict_types=1);

namespace Zodimo\Xml\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Zodimo\BaseReturnTest\MockClosureTrait;
use Zodimo\Xml\SaxParser;

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
        $parser = SaxParser::create();
        $this->assertInstanceOf(SaxParser::class, $parser);
    }

    public function testCanParseString1(): void
    {
        $xmlstring = '<root/>';
        $parser = SaxParser::create();
        $parser->registerCallback('/', $this->createClosureMock());
        $result = $parser->parseString($xmlstring, true);
        $this->assertTrue($result->isSuccess());
    }

    public function testCanParseString2(): void
    {
        $xmlstring = <<< 'XML'
            <root>
                <username>joe</username>
            </root>
            XML;

        $parser = SaxParser::create();
        $parser->registerCallback('/root/username', $this->createClosureMock());
        $result = $parser->parseString($xmlstring, true);
        $this->assertTrue($result->isSuccess());
    }

    // public function testCanPartialXml()
    // {
    //     $xmlstring = <<< 'XML'
    //         <root>
    //             <username>joe</username>
    //         </root>
    //         XML;

    //     $parser = SaxParser::create();
    //     $parser->registerCallback('/root/username', $this->createClosureMock());
    //     $result = $parser->parseString($xmlstring, true);
    //     $this->assertTrue($result->isSuccess());

    // }
}

<?php

declare(strict_types=1);

namespace Zodimo\Xml\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Zodimo\BaseReturnTest\MockClosureTrait;
use Zodimo\Xml\ExiParser;

/**
 * @internal
 *
 * @coversNothing
 */
class ExiParserTest extends TestCase
{
    use MockClosureTrait;

    public function testCanCreate(): void
    {
        $parserResult = ExiParser::create();
        $this->assertTrue($parserResult->isSuccess());
        $parser = $parserResult->unwrapSuccess($this->createClosureNotCalled());
        $this->assertInstanceOf(ExiParser::class, $parser);
    }
}

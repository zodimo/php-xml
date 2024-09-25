<?php

declare(strict_types=1);

namespace Zodimo\Xml;

use RuntimeException;
use Zodimo\BaseReturn\IOMonad;

class XPathParser
{
    // @phpstan-ignore property.onlyWritten
    private string $xPathExpression;

    private function __construct(
        string $xPathExpression
    ) {
        $this->xPathExpression = $xPathExpression;
    }

    public static function fromExpresstion(string $xPathExpression): XPathParser
    {
        return new XPathParser($xPathExpression);
    }

    /**
     * @return IOMonad<mixed,mixed>
     */
    public function parseFile(string $file): IOMonad
    {
        return IOMonad::fail(new RuntimeException('not yet implemented'));
    }

    /**
     * @return IOMonad<mixed,mixed>
     */
    public function parseString(string $data): IOMonad
    {
        return IOMonad::fail(new RuntimeException('not yet implemented'));
    }
}

<?php

declare(strict_types=1);

namespace Zodimo\Xml;

use Zodimo\BaseReturn\IOMonad;
use Zodimo\Xml\Errors\XmlParserException;

interface RegisterListenerInterface
{
    /**
     * @param callable(SimpleXmlReaderInterface):bool $callback
     *
     * @return IOMonad<XmlReaderParser,XmlParserException>
     */
    public function registerCallback(string $xpath, callable $callback, int $nodeType = \XMLReader::ELEMENT): IOMonad;
}

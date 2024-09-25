<?php

declare(strict_types=1);

namespace Zodimo\Xml;

use XMLReader;
use Zodimo\BaseReturn\IOMonad;
use Zodimo\Xml\Errors\XmlParserException;

interface UnRegisterListenerInterface
{
    /**
     * @return IOMonad<XmlReaderParser,XmlParserException>
     */
    public function unRegisterCallback(string $xpath, int $nodeType = XMLReader::ELEMENT): IOMonad;
}

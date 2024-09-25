<?php

declare(strict_types=1);

namespace Zodimo\Xml;

use DOMDocument;
use SimpleXMLElement;
use XMLReader;
use Zodimo\Xml\Errors\XmlParsingException;

/**
 * @mixin XMLReader
 */
interface SimpleXmlReaderInterface
{
    /**
     * Expand current node to string.
     *
     * @throws XmlParsingException
     */
    public function expandString(string $version = '1.0', string $encoding = 'UTF-8'): string;

    /**
     * Expand current node to SimpleXMLElement.
     *
     * @throws XmlParsingException
     */
    public function expandSimpleXml(string $version = '1.0', string $encoding = 'UTF-8', ?string $className = null): SimpleXMLElement;

    /**
     * Expand current node to DomDocument.
     *
     * @throws XmlParsingException
     */
    public function expandDomDocument(string $version = '1.0', string $encoding = 'UTF-8'): DOMDocument;
}

<?php

declare(strict_types=1);

namespace Zodimo\Xml;

use DOMDocument;
use SimpleXMLElement;
use Throwable;
use XMLReader;
use Zodimo\Xml\Errors\XmlParserException;
use Zodimo\Xml\Errors\XmlParsingException;

/**
 * @mixin XMLReader
 */
class NoOpSimpleXMLReader
{
    public const ANY_PATH = '*';

    /**
     * @var array<string,array<int,array<string,callable>>>
     */
    protected array $callbacks = [];

    protected XMLReader $reader;

    protected string $nodePath = '';

    private function __construct(XMLReader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * @param mixed $arguments
     *
     * @return mixed
     */
    public function __call(string $name, $arguments)
    {
        if (!empty($arguments)) {
            return $this->reader->{$name}($arguments);
        }

        return $this->reader->{$name}();
    }

    /**
     * @param mixed $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        return $this->reader->{$name};
    }

    /**
     * Add node callback.
     *
     * @param callable $callback
     */
    public function registerCallback(string $name, $callback, int $nodeType = XMLReader::ELEMENT): NoOpSimpleXMLReader
    {
        return $this->registerCallbackAtPath($name, $callback, self::ANY_PATH, $nodeType);
    }

    /**
     * @param callable $callback
     *
     * @throws XmlParserException
     */
    public function registerCallbackAtPath(string $name, $callback, string $path, int $nodeType = XMLReader::ELEMENT): NoOpSimpleXMLReader
    {
        $clone = clone $this;
        if (isset($clone->callbacks[$path][$nodeType][$name])) {
            throw XmlParserException::create("Already exists callback [path = {$path}] {$name}({$nodeType}).");
        }
        if (!is_callable($callback, true)) {
            throw XmlParserException::create('Callback is not callable.');
        }
        $clone->callbacks[$path][$nodeType][$name] = $callback;

        return $clone;
    }

    public function unRegisterCallback(string $name, int $nodeType = XMLReader::ELEMENT): NoOpSimpleXMLReader
    {
        return $this->unRegisterCallbackAtPath($name, self::ANY_PATH, $nodeType);
    }

    /**
     * @throws XmlParserException
     */
    public function unRegisterCallbackAtPath(string $name, string $path, int $nodeType = XMLReader::ELEMENT): NoOpSimpleXMLReader
    {
        $clone = clone $this;
        if (!isset($clone->callbacks[$path][$nodeType][$name])) {
            throw XmlParserException::create("Unknown parser callback [path = {$path}] {$name}({$nodeType}).");
        }
        unset($clone->callbacks[$path][$nodeType][$name]);

        return $clone;
    }

    /**
     * Run parser.
     *
     * @throws XmlParsingException
     */
    public function parse(): void
    {
        try {
            while ($this->reader->read()) {
                // no op
            }
        } catch (Throwable $e) {
            throw XmlParsingException::create('Read error', 0, $e);
        }
    }

    /**
     * Run XPath query on current node.
     *
     * @return null|array<SimpleXmlElement>|bool
     */
    public function expandXpath(string $path, string $version = '1.0', string $encoding = 'UTF-8')
    {
        return $this->expandSimpleXml($version, $encoding)->xpath($path);
    }

    /**
     * Expand current node to string.
     *
     * @return string
     *
     * @throws XmlParsingException
     */
    public function expandString(string $version = '1.0', string $encoding = 'UTF-8')
    {
        $simpleXmlElement = $this->expandSimpleXml($version, $encoding);
        // https://www.php.net/manual/en/simplexmlelement.asxml.php
        // If the filename isn't specified, this function returns a string on success and false on error.
        // If the parameter is specified, it returns true if the file was written successfully and false otherwise.
        $result = $simpleXmlElement->asXML();
        if (false === $result) {
            throw XmlParsingException::create('expandString failed on: SimpleXMLElement::asXML');
        }

        return $result;
    }

    /**
     * Expand current node to SimpleXMLElement.
     *
     * @throws XmlParsingException
     */
    public function expandSimpleXml(string $version = '1.0', string $encoding = 'UTF-8', ?string $className = null): SimpleXMLElement
    {
        if (is_null($className)) {
            $className = SimpleXMLElement::class;
        }
        // https://www.php.net/manual/en/xmlreader.expand.php
        // The resulting DOMNode or false on error.
        $element = $this->expand();
        if (false === $element) {
            throw XmlParsingException::create('expandSimpleXml failed on: DOMDocument::importNode');
        }

        /**
         * @todo look at https://www.php.net/manual/en/domimplementation.createdocument.php
         */
        $document = new DOMDocument($version, $encoding);

        // https://www.php.net/manual/en/domdocument.importnode.php
        // The copied node or false, if it cannot be copied.
        $node = $document->importNode($element, true);
        if (false === $node) {
            throw XmlParsingException::create('expandSimpleXml failed on: DOMDocument::importNode');
        }

        // https://www.php.net/manual/en/domnode.appendchild.php
        // The node added or false on error.
        $addedNode = $document->appendChild($node);
        if (false === $addedNode) {
            throw XmlParsingException::create('expandSimpleXml failed on: DOMDocument::appendChild');
        }

        // https://www.php.net/manual/en/function.simplexml-import-dom.php
        // Returns a SimpleXMLElement or null on failure.
        $result = simplexml_import_dom($node, $className);
        if (is_null($result)) {
            throw XmlParsingException::create('expandSimpleXml failed on: simplexml_import_dom');
        }

        return $result;
    }

    /**
     * Expand current node to DomDocument.
     *
     * @throws XmlParsingException
     */
    public function expandDomDocument(string $version = '1.0', string $encoding = 'UTF-8'): DOMDocument
    {
        // https://www.php.net/manual/en/xmlreader.expand.php
        // The resulting DOMNode or false on error.
        $element = $this->expand();
        if (false === $element) {
            throw XmlParsingException::create('expandDomDocument failed on: DOMDocument::importNode');
        }

        /**
         * @todo look at https://www.php.net/manual/en/domimplementation.createdocument.php
         */
        $document = new DOMDocument($version, $encoding);

        // https://www.php.net/manual/en/domdocument.importnode.php
        // The copied node or false, if it cannot be copied.
        $node = $document->importNode($element, true);
        if (false === $node) {
            throw XmlParsingException::create('expandDomDocument failed on: DOMDocument::importNode');
        }

        // https://www.php.net/manual/en/domnode.appendchild.php
        // The node added or false on error.
        $addedNode = $document->appendChild($node);
        if (false === $addedNode) {
            throw XmlParsingException::create('expandDomDocument failed on: DOMDocument::appendChild');
        }

        return $document;
    }

    /**
     * @throws XmlParsingException
     */
    public static function fromXmlFile(string $uri, ?string $encoding = null, int $flags = 0): NoOpSimpleXMLReader
    {
        /**
         * support for gzip.
         */
        $wrapGzip = function ($uri) {
            /**
             * @todo: do not be so naive
             */
            $file_parts = pathinfo($uri);
            if (key_exists('extension', $file_parts) and 'gz' == $file_parts['extension']) {
                return "compress.zlib://{$uri}";
            }

            return $uri;
        };

        $wrappedUri = call_user_func($wrapGzip, $uri);

        try {
            // https://www.php.net/manual/en/xmlreader.open.php
            // Returns true on success or false on failure. If called statically, returns an XMLReader or false on failure.

            $xmlReader = new XMLReader();
            $result = $xmlReader->open($wrappedUri, $encoding, $flags);
            if (false === $result) {
                throw XmlParsingException::create('Could not create XMLReader');
            }

            return new self($xmlReader);
        } catch (Throwable $e) {
            if ($e instanceof XmlParsingException) {
                throw $e;
            }

            throw XmlParsingException::create('XMLReader error', 0, $e);
        }
    }

    /**
     * @throws XmlParsingException
     */
    public static function fromXmlString(string $source, ?string $encoding = null, int $flags = 0): NoOpSimpleXMLReader
    {
        try {
            // https://www.php.net/manual/en/xmlreader.xml.php
            // Returns true on success or false on failure. If called statically, returns an XMLReader or false on failure.

            $xmlReader = new XMLReader();

            $result = $xmlReader->xml($source, $encoding, $flags);
            if (false === $result) {
                throw XmlParsingException::create('Could not create XMLReader');
            }

            return new self($xmlReader);
        } catch (Throwable $e) {
            if ($e instanceof XmlParsingException) {
                throw $e;
            }

            throw XmlParsingException::create('XMLReader error', 0, $e);
        }
    }

    public function getNodePath(): string
    {
        return $this->nodePath;
    }

    public function getPath(): string
    {
        return dirname($this->nodePath);
    }
}

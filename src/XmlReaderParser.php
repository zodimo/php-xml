<?php

declare(strict_types=1);

namespace Zodimo\Xml;

use DOMDocument;
use SimpleXMLElement;
use Throwable;
use XMLReader;
use Zodimo\BaseReturn\IOMonad;
use Zodimo\Xml\Errors\XmlParserException;
use Zodimo\Xml\Errors\XmlParsingException;

/**
 * @mixin XMLReader
 */
class XmlReaderParser implements XmlParserInterface, RegisterListenerInterface, UnRegisterListenerInterface, SimpleXmlReaderInterface
{
    /**
     * Do not remove redundant white space.
     *
     * @var bool
     */
    public $preserveWhiteSpace = true;

    protected XMLReader $reader;

    /**
     * @var array<int,array<string, callable>>
     */
    private array $callback = [];

    /**
     * Depth.
     *
     * @var int
     */
    private $currentDepth = 0;

    /**
     * Previos depth.
     *
     * @var int
     */
    private $prevDepth = 0;

    /**
     * @var array<int,string>
     */
    private array $nodesParsed = [];

    /**
     * @var array<int,int>
     */
    private array $nodesType = [];

    /**
     * @var array<int,int>
     */
    private array $nodesCounter = [];

    private function __construct()
    {
        $this->reader = new XMLReader();
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

    public static function create(): XmlReaderParser
    {
        return new self();
    }

    /**
     * @param array<string,mixed> $options
     *
     * @return IOMonad<void,XmlParserException|XmlParsingException>
     */
    public function parseFile(string $xmlFile, array $options = []): IOMonad
    {
        try {
            // https://www.php.net/manual/en/xmlreader.open.php
            // Returns true on success or false on failure. If called statically, returns an XMLReader or false on failure.
            $encoding = $options['encoding'] ?? null;
            // encodinig = string
            $flags = $options['flags'] ?? 0;
            // fags = int

            $result = $this->reader->open($xmlFile, $encoding, $flags);
            if (false === $result) {
                // @phpstan-ignore return.type
                return IOMonad::fail(XmlParsingException::create('Could not create XMLReader'));
            }
        } catch (Throwable $e) {
            if ($e instanceof XmlParsingException) {
                // @phpstan-ignore return.type
                return IOMonad::fail($e);
            }

            // @phpstan-ignore return.type
            return IOMonad::fail(XmlParsingException::create('XMLReader error', 0, $e));
        }

        return $this->parse();
    }

    /**
     * @param array<string,mixed> $options
     *
     * @return IOMonad<void,XmlParserException|XmlParsingException>
     */
    public function parseGzipFile(string $gzXmlFile, array $options = []): IOMonad
    {
        /**
         * support for gzip.
         */
        $wrapGzip = function (string $uri): string {
            /**
             * @todo: do not be so naive
             */
            $file_parts = pathinfo($uri);
            if (key_exists('extension', $file_parts) and 'gz' == $file_parts['extension']) {
                return "compress.zlib://{$uri}";
            }

            return $uri;
        };

        $wrappedUri = call_user_func($wrapGzip, $gzXmlFile);

        return $this->parseFile($wrappedUri, $options);
    }

    /**
     * Moves cursor to the next node in the document.
     *
     * @see http://php.net/manual/en/xmlreader.read.php
     *
     * @return bool returns TRUE on success or FALSE on failure
     */
    public function read()
    {
        $read = $this->reader->read();
        $readerDepth = $this->reader->depth;
        $localName = $this->reader->localName;
        $nodeType = $this->reader->nodeType;
        if ($readerDepth < $this->prevDepth) {
            if (!isset($this->nodesParsed[$readerDepth])) {
                throw XmlParsingException::create('Invalid xml: missing items in XmlReaderParser::$nodesParsed');
            }
            if (!isset($this->nodesCounter[$readerDepth])) {
                throw XmlParsingException::create('Invalid xml: missing items in XmlReaderParser::$nodesCounter');
            }
            if (!isset($this->nodesType[$readerDepth])) {
                throw XmlParsingException::create('Invalid xml: missing items in XmlReaderParser::$nodesType');
            }
            $this->nodesParsed = array_slice($this->nodesParsed, 0, $readerDepth + 1, true);
            $this->nodesCounter = array_slice($this->nodesCounter, 0, $readerDepth + 1, true);
            $this->nodesType = array_slice($this->nodesType, 0, $readerDepth + 1, true);
        }
        if (isset($this->nodesParsed[$readerDepth]) && $localName == $this->nodesParsed[$readerDepth] && $nodeType == $this->nodesType[$readerDepth]) {
            $this->nodesCounter[$readerDepth] = $this->nodesCounter[$readerDepth] + 1;
        } else {
            $this->nodesParsed[$readerDepth] = $localName;
            $this->nodesType[$readerDepth] = $nodeType;
            $this->nodesCounter[$readerDepth] = 1;
        }
        $this->prevDepth = $readerDepth;

        return $read;
    }

    /**
     * Return current xpath node.
     *
     * @param bool $nodesCounter
     *
     * @return string
     */
    public function currentXpath($nodesCounter = false)
    {
        if (count($this->nodesCounter) != count($this->nodesParsed) && count($this->nodesCounter) != count($this->nodesType)) {
            throw XmlParsingException::create('Path counter error');
        }
        $result = '';
        foreach ($this->nodesParsed as $depth => $name) {
            switch ($this->nodesType[$depth]) {
                case XMLReader::ELEMENT:
                    $result .= '/'.$name;
                    if ($nodesCounter) {
                        $result .= '['.$this->nodesCounter[$depth].']';
                    }

                    break;

                case XMLReader::TEXT:
                case XMLReader::CDATA:
                    $result .= '/text()';

                    break;

                case XMLReader::COMMENT:
                    $result .= '/comment()';

                    break;

                case XMLReader::ATTRIBUTE:
                    $result .= "[@{$name}]";

                    break;
            }
        }

        return $result;
    }

    /**
     * @param callable(SimpleXmlReaderInterface):bool $callback
     *
     * @return IOMonad<XmlReaderParser,XmlParserException>
     */
    public function registerCallback(string $xpath, callable $callback, int $nodeType = XMLReader::ELEMENT): IOMonad
    {
        $clone = clone $this;
        if (isset($clone->callback[$nodeType][$xpath])) {
            return IOMonad::fail(XmlParserException::create("Already exists callback '{$xpath}':{$nodeType}."));
        }
        if (!is_callable($callback)) {
            return IOMonad::fail(XmlParserException::create("Not callable callback '{$xpath}':{$nodeType}."));
        }
        $clone->callback[$nodeType][$xpath] = $callback;

        // @phpstan-ignore return.type
        return IOMonad::pure($clone);
    }

    /**
     * Run XPath query on current node.
     *
     * @return array<SimpleXmlElement>
     */
    public function expandXpath(string $path, string $version = '1.0', string $encoding = 'UTF-8'): array
    {
        $result = $this->expandSimpleXml($version, $encoding)->xpath($path);
        if (!is_array($result)) {
            throw XmlParsingException::create('expandString failed on: SimpleXMLElement::asXML');
        }

        return $result;
    }

    /**
     * Expand current node to string.
     *
     * @throws XmlParsingException
     */
    public function expandString(string $version = '1.0', string $encoding = 'UTF-8'): string
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
        $element = $this->reader->expand();
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
     * Summary of unRegisterCallback.
     *
     * @return IOMonad<XmlReaderParser,XmlParserException>
     */
    public function unRegisterCallback(string $xpath, int $nodeType = XMLReader::ELEMENT): IOMonad
    {
        if (!isset($this->callback[$nodeType][$xpath])) {
            return IOMonad::fail(XmlParserException::create("Unknow parser callback '{$xpath}':{$nodeType}."));
        }

        $clone = clone $this;
        unset($clone->callback[$nodeType][$xpath]);

        // @phpstan-ignore return.type
        return IOMonad::pure($clone);
    }

    /**
     * @return IOMonad<void,XmlParserException|XmlParsingException>
     */
    private function parse(): IOMonad
    {
        if (empty($this->callback)) {
            // @phpstan-ignore return.type
            return IOMonad::fail(XmlParserException::create('Empty parser callback.'));
        }

        $continue = true;

        try {
            // NOTE: wrapping read with try catch instead of wrapping each read with IOMonad
            $continue = true;
            while ($continue && $this->read()) {
                $nodeType = $this->reader->nodeType;
                $qualifiedName = $this->reader->name;
                if (!isset($this->callback[$nodeType])) {
                    continue;
                }
                if (isset($this->callback[$nodeType][$qualifiedName])) {
                    $continue = call_user_func($this->callback[$nodeType][$qualifiedName], $this);
                } else {
                    $xpath = $this->currentXpath(false); // without node counter
                    if (isset($this->callback[$nodeType][$xpath])) {
                        $continue = call_user_func($this->callback[$nodeType][$xpath], $this);
                    } else {
                        $xpath = $this->currentXpath(true); // with node counter
                        if (isset($this->callback[$nodeType][$xpath])) {
                            $continue = call_user_func($this->callback[$nodeType][$xpath], $this);
                        }
                    }
                }
            }
        } catch (Throwable $e) {
            if ($e instanceof XmlParsingException) {
                // @phpstan-ignore return.type
                return IOMonad::fail($e);
            }

            // @phpstan-ignore return.type
            return IOMonad::fail(XmlParsingException::create('XMLReader error', 0, $e));
        }

        // @phpstan-ignore return.type
        return IOMonad::pure(null);
    }
}

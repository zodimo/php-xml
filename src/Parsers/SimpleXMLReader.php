<?php

declare(strict_types=1);

namespace Zodimo\Xml\Parsers;

use DOMDocument;
use SimpleXMLElement;
use Throwable;
use XMLReader;
use Zodimo\BaseReturn\IOMonad;
use Zodimo\Xml\Errors\XmlParserException;
use Zodimo\Xml\Errors\XmlParsingException;
use Zodimo\Xml\Traits\HandlerInfrastructure;
use Zodimo\Xml\Traits\XmlReaderCallbackInfrastructure;

/**
 * @mixin XMLReader
 *
 * @implements XmlReaderParserInterface<\Throwable>
 */
class SimpleXMLReader implements XmlReaderParserInterface, HasHandlers
{
    /**
     * @phpstan-use HandlerInfrastructure<\Throwable>
     */
    use HandlerInfrastructure;

    /**
     * @phpstan-use XmlReaderCallbackInfrastructure<\Throwable>
     */
    use XmlReaderCallbackInfrastructure;
    public const ANY_PATH = '*';

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
     * Run parser.
     *
     * @throws XmlParsingException
     * @throws XmlParserException
     */
    public function parse(): void
    {
        if (empty($this->callbacks)) {
            throw XmlParserException::create('Empty parser callback.');
        }
        $continue = true;

        try {
            while ($continue && $this->reader->read()) {
                if (XMLReader::ELEMENT == $this->reader->nodeType) {
                    $this->nodePath .= '/'.$this->reader->name;
                }

                // keep track of depth and path to know.
                // if callback exist at path else any level

                $currentPath = $this->getPath();

                if (isset($this->callbacks[$currentPath][$this->reader->nodeType][$this->reader->name])) {
                    $continue = call_user_func($this->callbacks[$currentPath][$this->reader->nodeType][$this->reader->name], $this);
                } elseif (isset($this->callbacks[self::ANY_PATH][$this->reader->nodeType][$this->reader->name])) {
                    $continue = call_user_func($this->callbacks[self::ANY_PATH][$this->reader->nodeType][$this->reader->name], $this);
                }

                // check isEmptyElement for self-closing tag

                if (XMLReader::END_ELEMENT == $this->reader->nodeType || $this->reader->isEmptyElement) {
                    // may pose problems
                    $tail = preg_quote('/'.$this->reader->name, '/');
                    $pattern = "/{$tail}$/";
                    $newPath = preg_replace($pattern, '', $this->nodePath);
                    if (!is_string($newPath)) {
                        throw XmlParsingException::create('Could not reduce path on closing tag');
                    }
                    $this->nodePath = $newPath;
                }
            }
        } catch (Throwable $e) {
            throw XmlParsingException::create('Read error', 0, $e);
        }
    }

    /**
     * Run XPath query on current node.
     *
     * @return array<SimpleXmlElement>
     */
    public function expandXpath(string $path, string $version = '1.0', string $encoding = 'UTF-8')
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
    public static function fromXmlFile(string $uri, ?string $encoding = null, int $flags = 0): SimpleXMLReader
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
    public static function fromXmlString(string $source, ?string $encoding = null, int $flags = 0): SimpleXMLReader
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

    public function parseFile(string $file): IOMonad
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

        $wrappedUri = call_user_func($wrapGzip, $file);

        try {
            // https://www.php.net/manual/en/xmlreader.xml.php
            // Returns true on success or false on failure. If called statically, returns an XMLReader or false on failure.
            $result = $this->reader->open($wrappedUri);

            if (false === $result) {
                return IOMonad::fail(XmlParsingException::create('Could not create load xml'));
            }
        } catch (Throwable $e) {
            if ($e instanceof XmlParsingException) {
                return IOMonad::fail($e);
            }

            return IOMonad::fail(XmlParsingException::create('XMLReader error', 0, $e));
        }

        try {
            $this->parse();

            // @phpstan-ignore return.type
            return IOMonad::pure($this);
        } catch (Throwable $e) {
            // @phpstan-ignore return.type
            return IOMonad::fail($e);
        }
    }

    public function parseString(string $data, bool $isFinal): IOMonad
    {
        try {
            // https://www.php.net/manual/en/xmlreader.xml.php
            // Returns true on success or false on failure. If called statically, returns an XMLReader or false on failure.

            $result = $this->reader->xml($data);
            if (false === $result) {
                return IOMonad::fail(XmlParsingException::create('Could not create load xml'));
            }
        } catch (Throwable $e) {
            if ($e instanceof XmlParsingException) {
                return IOMonad::fail($e);
            }

            return IOMonad::fail(XmlParsingException::create('XMLReader error', 0, $e));
        }

        try {
            $this->parse();

            // @phpstan-ignore return.type
            return IOMonad::pure($this);
        } catch (Throwable $e) {
            // @phpstan-ignore return.type
            return IOMonad::fail($e);
        }
    }

    /**
     * @return IOMonad<self,string>
     */
    public static function create(): IOMonad
    {
        return IOMonad::pure(new self(new XMLReader()));
    }
}

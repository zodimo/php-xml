<?php

declare(strict_types=1);

namespace Zodimo\Xml;

use SimpleXMLElement;
use Throwable;
use Webmozart\Assert\Assert;
use Zodimo\BaseReturn\IOMonad;
use Zodimo\Xml\Errors\XmlParserException;
use Zodimo\Xml\Errors\XmlParsingException;

class XPathParser implements XmlParserInterface
{
    private string $xpath;

    /**
     * @var callable(SimpleXMLElement):bool
     */
    private $callback;

    private function __construct(
        string $xpath,
        callable $callback
    ) {
        $this->xpath = $xpath;
        $this->callback = $callback;
    }

    public static function create(string $xpath, callable $callback): XPathParser
    {
        return new self($xpath, $callback);
    }

    /**
     * @param array<string,mixed> $options
     *
     * @return IOMonad<void,XmlParserException|XmlParsingException>
     */
    public function parseFile(string $xmlFile, array $options = []): IOMonad
    {
        try {
            $className = $options['className'] ?? SimpleXMLElement::class;
            Assert::stringNotEmpty($className, 'className cannot be empty');
            $libXmlOptions = $options['options'] ?? 0;
            Assert::greaterThanEq($libXmlOptions, 0, 'Expects positive int: Bitwise OR of the libxml option constants.');
            $namespaceOrPrefix = $options['namespaceOrPrefix'] ?? '';
            Assert::string($namespaceOrPrefix, 'String expected');
            $isPrefix = $options['isPrefix'] ?? false;
            Assert::boolean($isPrefix, 'Boolean expected');

            $simpleXml = simplexml_load_file($xmlFile, $className, $libXmlOptions, $namespaceOrPrefix, $isPrefix);
            if (!$simpleXml instanceof SimpleXMLElement) {
                // @phpstan-ignore return.type
                return IOMonad::fail(XmlParsingException::create('Could not load file'));
            }
            $xpathResultSet = $simpleXml->xpath($this->xpath);

            if (!is_array($xpathResultSet)) {
                // @phpstan-ignore return.type
                return IOMonad::fail(XmlParsingException::create('Could not evaluate xpath'));
            }
            $continue = true;
            foreach ($xpathResultSet as $xpathResult) {
                if (true === $continue) {
                    $cb = $this->callback;
                    $continue = $cb($xpathResult);
                } else {
                    break;
                }
            }

            // @phpstan-ignore return.type
            return IOMonad::pure(null);
        } catch (Throwable $e) {
            if ($e instanceof XmlParsingException) {
                // @phpstan-ignore return.type
                return IOMonad::fail($e);
            }

            // @phpstan-ignore return.type
            return IOMonad::fail(XmlParsingException::create('XPathParser error', 0, $e));
        }
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
}

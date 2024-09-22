<?php

declare(strict_types=1);

namespace Zodimo\Xml;

use Zodimo\BaseReturn\IOMonad;
use Zodimo\BaseReturn\Option;
use Zodimo\Xml\Traits\HandlersTrait;

/**
 * Currently broken implementation.
 *
 * @implements XmlParserInterface<\Throwable>
 */
class NoOpParser implements XmlParserInterface, HasHandlers
{
    /**
     * @phpstan-use HandlersTrait<\Throwable>
     */
    use HandlersTrait;

    /**
     * @var resource|\XMLParser
     *
     * @phpstan-ignore class.notFound
     */
    private $parser;

    /**
     * Defines how much bytes to read from file per iteration.
     *
     * @var int<1,max>
     */
    private $readBuffer = 8192;

    /**
     * @param resource|\XMLParser $parser
     *
     * @phpstan-ignore class.notFound
     */
    private function __construct($parser)
    {
        $this->parser = $parser;
    }

    /**
     * @return IOMonad<self,string>
     */
    public static function create(): IOMonad
    {
        $mParser = new self(xml_parser_create('UTF-8'));
        $mParserResult = $mParser->setOption(XML_OPTION_CASE_FOLDING, 0)
            ->flatMap(fn ($sparser) => $sparser->setOption(XML_OPTION_SKIP_WHITE, 1))
        ;

        // @phpstan-ignore argument.type
        xml_set_object($mParser->parser, $mParser);

        // @phpstan-ignore argument.type
        xml_set_element_handler($mParser->parser, [$mParser, 'startTag'], [$mParser, 'endTag']);
        // @phpstan-ignore argument.type
        xml_set_character_data_handler($mParser->parser, [$mParser, 'tagData']);

        // xml_set_external_entity_ref_handler($this->parser, 'convertEntities');

        return $mParserResult;
    }

    /**
     * Set option to XML parser.
     *
     * @param bool|int|string $value
     *
     * @return IOMonad<self,string>
     *
     * @see XML_OPTION_* constants
     * @see http://php.net/manual/en/function.xml-parser-set-option.php
     */
    public function setOption(int $option, $value): IOMonad
    {
        // @phpstan-ignore argument.type
        $result = xml_parser_set_option($this->parser, $option, $value);
        if (false === $result) {
            return IOMonad::fail('Could not set option');
        }

        // @phpstan-ignore return.type
        return IOMonad::pure($this);
    }

    /**
     * Get option from XML parser.
     *
     * @see XML_OPTION_* constants
     * @see http://php.net/manual/en/function.xml-parser-set-option.php
     *
     * @return bool|int|string
     */
    public function getParserOption(int $option)
    {
        // @phpstan-ignore argument.type
        return xml_parser_get_option($this->parser, $option);
    }

    public function getReadBuffer(): int
    {
        return $this->readBuffer;
    }

    /**
     * @return IOMonad<self,string>
     */
    public function setReadBuffer(int $readBuffer): IOMonad
    {
        if ($readBuffer < 1) {
            return IOMonad::fail('Readbuffer must be larger than 1');
        }
        $this->readBuffer = $readBuffer;

        // @phpstan-ignore return.type
        return IOMonad::pure($this);
    }

    /**
     * Handles start tag.
     * start_element_handler(XMLParser $parser, string $name, array $attributes): void.
     *
     * @param mixed               $_
     * @param array<string,mixed> $attributes
     */
    public function startTag($_, string $name, array $attributes): void {}

    /**
     * Handles close tag.
     *
     * @param mixed $_
     */
    public function endTag($_, string $name): void {}

    /**
     * Handles tag content.
     * handler(XMLParser $parser, string $data): void.
     *
     * @param mixed $_
     */
    public function tagData($_, string $data): void {}

    /**
     * @return IOMonad<HasHandlers,mixed>
     */
    public function parseString(string $data, bool $isFinal): IOMonad
    {
        // @phpstan-ignore argument.type
        $result = xml_parse($this->parser, $data, $isFinal);

        if ($isFinal) {
            // @phpstan-ignore argument.type
            xml_parser_free($this->parser);
        }

        if (1 === $result) {
            // success
            // @phpstan-ignore return.type
            return IOMonad::pure($this);
        }

        // failure
        // For unsuccessful parses, error information can be retrieved with

        /**
         * @todo give better errors
         */

        // $errorCode = xml_get_error_code($this->parser);
        // $errorString = xml_error_string($errorCode);
        // $lineNumber = xml_get_current_line_number($this->parser);
        // $columnNumber = xml_get_current_column_number($this->parser);

        // xml_get_current_byte_index().
        return IOMonad::fail('something happened');
    }

    /**
     * Support xml and xml.gz.
     *
     * @return IOMonad<HasHandlers,mixed>
     */
    public function parseFile(string $file): IOMonad
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

        $wrappedFile = $wrapGzip($file);

        $handle = fopen($wrappedFile, 'r');
        if (!$handle) {
            return IOMonad::fail(new \Exception('Unable to open file.'));
        }
        $result = IOMonad::pure($this);
        while (!feof($handle) and $result->isSuccess()) {
            $data = fread($handle, $this->readBuffer);
            if (false === $data) {
                break;
            }
            $result = $this->parseString($data, feof($handle));
        }

        fclose($handle);

        // @phpstan-ignore return.type
        return $result;
    }
}

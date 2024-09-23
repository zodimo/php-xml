<?php

declare(strict_types=1);

namespace Zodimo\Xml;

use Exception;
use Throwable;
use XMLParser;
use Zodimo\BaseReturn\IOMonad;
use Zodimo\BaseReturn\Option;
use Zodimo\Xml\EXI\ExiEvent;
use Zodimo\Xml\Traits\HandlersTrait;

/**
 * @implements XmlParserInterface<\Throwable>
 */
class ExiXmlParser implements XmlParserInterface, HasHandlers
{
    /**
     * @phpstan-use HandlersTrait<\Throwable>
     */
    use HandlersTrait;

    /**
     * @var resource|XMLParser
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
     * @var array<string>
     */
    private array $path;

    private ?string $collectingFromPath;

    /**
     * if is it set it means we are collecting.
     *
     * @var ?callable(ExiEvent):void
     */
    private $activeCallback;

    private string $pathString;

    /**
     * @param resource|XMLParser $parser
     *
     * @phpstan-ignore class.notFound
     */
    private function __construct($parser)
    {
        $this->parser = $parser;

        $this->path = [''];
        $this->pathString = '';

        $this->activeCallback = null;
        $this->collectingFromPath = null;
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

    // *****************
    // INTERNAL HANDLERS.
    // *****************

    /**
     * Handles start tag.
     * start_element_handler(XMLParser $parser, string $name, array $attributes): void.
     *
     * @param resource|XMLParser  $parser
     * @param array<string,mixed> $attributes
     *
     * @phpstan-ignore class.notFound
     */
    public function startTag($parser, string $name, array $attributes): void
    {
        $this->path[] = $name;
        $this->pathString = implode('/', $this->path);

        if (is_null($this->activeCallback)) {
            $this->activeCallback = $this->getHandlerForPath($this->pathString)->match(
                fn (callable $cb) => $cb,
                fn () => null
            );
            if (!is_null($this->activeCallback)) {
                $this->collectingFromPath = $this->pathString;
            }
        }

        if (!is_null($this->activeCallback)) {
            $handler = $this->activeCallback;
            $handler(ExiEvent::startElement($name));

            foreach ($attributes as $attributeName => $attributeValue) {
                $handler(ExiEvent::attribute($attributeName, $attributeValue));
            }
        }
    }

    /**
     * Handles close tag.
     *
     * @param resource|XMLParser $parser
     *
     * @phpstan-ignore class.notFound
     */
    public function endTag($parser, string $name): void
    {
        if (!is_null($this->activeCallback)) {
            $handler = $this->activeCallback;
            $handler(ExiEvent::endElement());
        }

        if ($this->isCollectionPath($this->pathString)) {
            $this->activeCallback = null;
            $this->collectingFromPath = null;
        }
        // $this->popNodeFromPath();

        array_pop($this->path);
        $this->pathString = implode('/', $this->path);
    }

    /**
     * Handles tag content.
     * handler(XMLParser $parser, string $data): void.
     *
     * @param resource|XMLParser $parser
     *
     * @phpstan-ignore class.notFound
     */
    public function tagData($parser, string $data): void
    {
        if (!is_null($this->activeCallback)) {
            $handler = $this->activeCallback;
            $handler(ExiEvent::characters($data));
        }
    }

    // *************************
    // END OF INTERNAL HANDLERS.
    // *************************
    /**
     * @return IOMonad<HasHandlers,mixed>
     */
    public function parseString(string $data, bool $isFinal): IOMonad
    {
        // if (0 == count($this->callbacks)) {
        //     // we dont need to do anything, nobody is there to observe
        //     // you probably think you are observing, so this should be an error state
        //     return IOMonad::fail('No callbacks registered');
        // }

        try {
            // @phpstan-ignore argument.type
            $result = xml_parse($this->parser, $data, $isFinal);
        } catch (Throwable $e) {
            // @phpstan-ignore argument.type
            xml_parser_free($this->parser);

            return IOMonad::fail($e);
        }

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
            return IOMonad::fail(new Exception('Unable to open file.'));
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

    private function isCollectionPath(string $path): bool
    {
        if (!is_null($this->collectingFromPath)) {
            return false;
        }

        return $this->collectingFromPath == $path;
    }

    // @phpstan-ignore method.unused
    private function hasHandler(string $path): bool
    {
        /**
         * path / includes /roots.
         */
        foreach ($this->callbacks as $hpath => $handler) {
            if (0 === strpos($path, $hpath)) {
                return true;
            }
        }

        return key_exists($path, $this->callbacks);
    }

    /**
     * @return Option<callable>
     */
    private function getHandlerForPath(string $path): Option
    {
        foreach ($this->callbacks as $hpath => $handler) {
            if (0 === strpos($path, $hpath)) {
                return Option::some($this->callbacks[$hpath]);
            }
        }

        // this should not happen.. the hasHandler has checked already for its existence;
        return Option::none();
    }
}

<?php

declare(strict_types=1);

namespace Zodimo\Xml\Parsers;

use Exception;
use RuntimeException;
use Throwable;
use XMLParser;
use Zodimo\BaseReturn\IOMonad;
use Zodimo\BaseReturn\Option;
use Zodimo\Xml\Enums\XmlNodeTypes;
use Zodimo\Xml\Traits\HandlerInfrastructure;
use Zodimo\Xml\Traits\SaxCallbackInfrastructure;
use Zodimo\Xml\Value\XmlValue;
use Zodimo\Xml\Value\XmlValueBuilder;

/**
 * Currently broken implementation.
 *
 * @implements SaxParserInterface<\Throwable>
 */
class SaxParser implements SaxParserInterface, HasHandlers
{
    /**
     * @phpstan-use HandlerInfrastructure<\Throwable>
     */
    use HandlerInfrastructure;

    /**
     * @phpstan-use SaxCallbackInfrastructure<\Throwable>
     */
    use SaxCallbackInfrastructure;

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

    private bool $isCollecting;

    /**
     * @var Option<string>
     */
    private Option $collectingFrom;

    /**
     * @var Option<XmlValueBuilder>
     */
    private Option $collectedData;

    /**
     * @param resource|XMLParser $parser
     *
     * @phpstan-ignore class.notFound
     */
    private function __construct($parser)
    {
        $this->parser = $parser;

        $this->path = [''];
        $this->collectedData = Option::none();
        $this->isCollecting = false;
        $this->collectingFrom = Option::none();
    }

    /**
     * @return IOMonad<self,string>
     */
    public static function create(): IOMonad
    {
        $mParser = new self(xml_parser_create('UTF-8'));

        return $mParser->setOption(XML_OPTION_CASE_FOLDING, 0)
            ->flatMap(fn ($sparser) => $sparser->setOption(XML_OPTION_SKIP_WHITE, 1))
        ;
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
    public function startTag($_, string $name, array $attributes): void
    {
        // $this->currentTag = $name;

        $this->addNodeToPath($name);

        if ($this->isCollecting) {
            // just append...
            $this->collectedData = $this->collectedData->map(fn ($valueBuilder) => $valueBuilder->addChild(XmlValueBuilder::create($name, $attributes)));
        } elseif ($this->hasHandler($this->pathAsString())) {
            // cannot add addition handlers when already collecting..
            $this->collectingFrom = Option::some($this->pathAsString());
            $xmlValue = XmlValueBuilder::create($name, $attributes);

            $this->collectedData = Option::some($xmlValue);

            $this->isCollecting = true;
        }
    }

    /**
     * Handles close tag.
     *
     * @param mixed $_
     */
    public function endTag($_, string $name): void
    {
        $path = $this->pathAsString();

        if ($this->isCollectionPath($path)) {
            // handle the data
            // reset the data
            $collectedDataOption = $this->collectedData->match(
                fn ($builder) => Option::some($builder->build()),
                fn () => Option::none()
            );

            $result = $this->callHandlerWithData($collectedDataOption);
            if ($result->isFailure()) {
                $error = $result->unwrapFailure(fn ($_) => new RuntimeException('BUG, false positive on callback failure'));
                if ($error instanceof Throwable) {
                    throw $error;
                }

                throw new RuntimeException((string) $error);
            }
            $this->isCollecting = false;
            $this->collectingFrom = Option::none();
            $this->collectedData = Option::none();
        }

        $tail = preg_quote('/'.$name, '/');
        $pattern = "/{$tail}$/";
        $path = preg_replace($pattern, '', $path);

        /**
         * @todo handle the $path errors better
         */
        // @phpstan-ignore argument.type
        $this->setPathFromString($path);
    }

    /**
     * Handles tag content.
     * handler(XMLParser $parser, string $data): void.
     *
     * @param mixed $_
     */
    public function tagData($_, string $data): void
    {
        /**
         * Skip white space
         * and the concatenate all non empty parts.
         */
        if ($this->isCollecting) {
            $trimmedData = trim($data);
            if (0 === mb_strlen($trimmedData)) {
                return;
            }

            /**
             * data path..
             * path /root/user/name
             * data starts at user.
             *
             *  We need something like a lens or a cursor
             *
             * collectedData can be deeptly nested..
             */
            $this->collectedData = $this->collectedData->map(fn ($valueBuilder) => $valueBuilder->addValue($data));
        }
    }

    /**
     * @return IOMonad<HasHandlers,mixed>
     */
    public function parseString(string $data, bool $isFinal): IOMonad
    {
        if (0 == count($this->callbacks)) {
            // we dont need to do anything, nobody is there to observe
            // you probably think you are observing, so this should be an error state
            return IOMonad::fail('No callbacks registered');
        }

        /**
         * We configure this here because of imutabilty during callback and handler processing.
         */

        // @phpstan-ignore argument.type
        xml_set_object($this->parser, $this);
        // @phpstan-ignore argument.type
        xml_set_element_handler($this->parser, [$this, 'startTag'], [$this, 'endTag']);
        // @phpstan-ignore argument.type
        xml_set_character_data_handler($this->parser, [$this, 'tagData']);

        // xml_set_external_entity_ref_handler($this->parser, 'convertEntities');

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

    private function pathAsString(): string
    {
        return implode('/', $this->path);
    }

    private function setPathFromString(string $path): void
    {
        $this->path = explode('/', $path);
    }

    private function isCollectionPath(string $path): bool
    {
        return $this->collectingFrom->match(
            fn (string $collecingFromPath) => $collecingFromPath == $path,
            fn () => false,
        );
    }

    private function addNodeToPath(string $name): void
    {
        $this->path[] = $name;
    }

    /**
     * @return Option<callable(mixed):void>>
     */
    private function getHandlerForPath(string $path): Option
    {
        $nodeType = XmlNodeTypes::NODE();
        if ($this->hasHandler($path)) {
            foreach ($this->callbacks as $hpath => $callbackRegistrationId) {
                if (0 === strpos($path, $hpath)) {
                    return Option::some($this->callbackRegistrations[$callbackRegistrationId]->getCallback());
                }
            }

            // this should not happen.. the hasHandler has checked already for its existence;
            return Option::none();
        }

        return Option::none();
    }

    /**
     * @param Option<XmlValue> $dataOption
     *
     * @return IOMonad<void,Throwable>
     */
    private function callHandlerWithData(Option $dataOption): IOMonad
    {
        $path = $this->pathAsString();

        // only call the callback if we actually have data
        // @phpstan-ignore return.type
        return $dataOption->match(
            fn ($data) => $this->getHandlerForPath($path)->match(
                function ($handler) use ($data) {
                    // ignore return value from callback
                    return IOMonad::try(fn () => $handler($data))->fmap(fn ($_) => null);
                },
                fn () => IOMonad::pure(null)
            ),
            fn () => IOMonad::pure(null)
        );
    }
}

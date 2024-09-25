<?php

declare(strict_types=1);

namespace Zodimo\Xml;

use Zodimo\BaseReturn\IOMonad;
use Zodimo\Xml\Errors\XmlParserException;
use Zodimo\Xml\Errors\XmlParsingException;

interface XmlParserInterface
{
    /**
     * @param array<string,mixed> $options
     *
     * @return IOMonad<void,XmlParserException|XmlParsingException>
     */
    public function parseFile(string $xmlFile, array $options = []): IOMonad;

    /**
     * @param array<string,mixed> $options
     *
     * @return IOMonad<void,XmlParserException|XmlParsingException>
     */
    public function parseGzipFile(string $gzXmlFile, array $options = []): IOMonad;
}

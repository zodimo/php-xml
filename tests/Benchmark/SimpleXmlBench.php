<?php

declare(strict_types=1);

namespace Zodimo\Xml\Tests\Benchmark;

use RuntimeException;
use SimpleXMLElement;

/**
 * @internal
 *
 * @coversNothing
 */
class SimpleXmlBench
{
    public function getCallback(): callable
    {
        return fn ($_) => true;
    }

    public function wrapGZIP(string $uri): string
    {
        $file_parts = pathinfo($uri);
        if (key_exists('extension', $file_parts) and 'gz' == $file_parts['extension']) {
            return "compress.zlib://{$uri}";
        }

        return $uri;
    }

    public function benchParseFile1k(): void
    {
        $filePath = __DIR__.'/../Resources/1k.xml.gz';
        $simpleXml = simplexml_load_file($this->wrapGZIP($filePath));
        if ($simpleXml instanceof SimpleXMLElement) {
            $result = $simpleXml->xpath('/root/user');
        } else {
            throw new RuntimeException('not simplexml element');
        }
    }

    public function benchParseFile10k(): void
    {
        $filePath = __DIR__.'/../Resources/10k.xml.gz';
        $simpleXml = simplexml_load_file($this->wrapGZIP($filePath));
        if ($simpleXml instanceof SimpleXMLElement) {
            $result = $simpleXml->xpath('/root/user');
        } else {
            throw new RuntimeException('not simplexml element');
        }
    }

    public function benchParseFile100k(): void
    {
        $filePath = __DIR__.'/../Resources/100k.xml.gz';
        $simpleXml = simplexml_load_file($this->wrapGZIP($filePath));
        if ($simpleXml instanceof SimpleXMLElement) {
            $result = $simpleXml->xpath('/root/user');
        } else {
            throw new RuntimeException('not simplexml element');
        }
    }

    public function benchParseFile1m(): void
    {
        $filePath = __DIR__.'/../Resources/1m.xml.gz';
        $simpleXml = simplexml_load_file($this->wrapGZIP($filePath));
        if ($simpleXml instanceof SimpleXMLElement) {
            $result = $simpleXml->xpath('/root/user');
        } else {
            throw new RuntimeException('not simplexml element');
        }
    }
}

<?php

declare(strict_types=1);

namespace Zodimo\Xml\Tests\Unit\Parsers;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use SimpleXMLElement;

/**
 * @internal
 *
 * @coversNothing
 */
class SimpleXmlTest extends TestCase
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

    // public function testParseFile1k(): void
    // {
    //     $filePath = __DIR__.'/../Resources/1k.xml.gz';
    //     $simpleXml = simplexml_load_file($this->wrapGZIP($filePath));
    //     $result = null;
    //     if ($simpleXml instanceof SimpleXMLElement) {
    //         $result = $simpleXml->xpath('/root/user');
    //     } else {
    //         throw new RuntimeException('not simplexml element');
    //     }
    //     $this->assertCount(1000, $result);
    // }

    // public function testParseFile10k(): void
    // {
    //     $filePath = __DIR__.'/../Resources/10k.xml.gz';
    //     $simpleXml = simplexml_load_file($this->wrapGZIP($filePath));
    //     $result = null;
    //     if ($simpleXml instanceof SimpleXMLElement) {
    //         $result = $simpleXml->xpath('/root/user');
    //     } else {
    //         throw new RuntimeException('not simplexml element');
    //     }
    //     $this->assertCount(10000, $result);
    // }

    // public function testParseFile100k(): void
    // {
    //     $filePath = __DIR__.'/../Resources/100k.xml.gz';
    //     $simpleXml = simplexml_load_file($this->wrapGZIP($filePath));
    //     $result = null;
    //     if ($simpleXml instanceof SimpleXMLElement) {
    //         $result = $simpleXml->xpath('/root/user');
    //     } else {
    //         throw new RuntimeException('not simplexml element');
    //     }
    //     $this->assertCount(100000, $result);
    // }

    // public function testParseFile1m(): void
    // {
    //     $filePath = __DIR__.'/../Resources/1m.xml.gz';
    //     $simpleXml = simplexml_load_file($this->wrapGZIP($filePath));
    //     $result = null;
    //     if ($simpleXml instanceof SimpleXMLElement) {
    //         $result = $simpleXml->xpath('/root/user');
    //     } else {
    //         throw new RuntimeException('not simplexml element');
    //     }
    //     $this->assertCount(1000000, $result);
    // }

    public function testParseFile1mString(): void
    {
        $filePath = __DIR__.'/../Resources/1m.xml.gz';
        $simpleXml = simplexml_load_file($this->wrapGZIP($filePath));
        $result = null;
        if ($simpleXml instanceof SimpleXMLElement) {
            $result = $simpleXml->xpath('/root/user');
        } else {
            throw new RuntimeException('not simplexml element');
        }
        $this->assertCount(1000000, $result);
    }
}

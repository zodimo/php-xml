<?php

declare(strict_types=1);

namespace Zodimo\Xml\Errors;

class XmlParserException extends \Exception implements \Throwable
{
    public static function create(string $message, int $code = 0, ?\Throwable $previous = null): XmlParserException
    {
        return new self($message, $code, $previous);
    }
}

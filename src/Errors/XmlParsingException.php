<?php

declare(strict_types=1);

namespace Zodimo\Xml\Errors;

class XmlParsingException extends \Exception implements \Throwable
{
    public static function create(string $message, int $code = 0, ?\Throwable $previous = null): XmlParsingException
    {
        return new self($message, $code, $previous);
    }
}

<?php

declare(strict_types=1);

namespace Zodimo\Xml;

class CallbackRegistration
{
    private string $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public static function create(string $path): CallbackRegistration
    {
        return new self($path);
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function asIdString(): string
    {
        return $this->getPath();
    }
}

<?php

declare(strict_types=1);

namespace Zodimo\Xml;

class CallbackRegistration
{
    private string $path;

    /**
     * @var callable
     */
    private $callback;

    public function __construct(string $path, callable $callback)
    {
        $this->path = $path;
        $this->callback = $callback;
    }

    public static function create(string $path, callable $callback): CallbackRegistration
    {
        return new self($path, $callback);
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function asIdString(): string
    {
        return $this->getPath();
    }

    public function getCallback(): callable
    {
        return $this->callback;
    }
}

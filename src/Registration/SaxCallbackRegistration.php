<?php

declare(strict_types=1);

namespace Zodimo\Xml\Registration;

use Zodimo\Xml\Enums\XmlNodeTypes;

/**
 * @implements CallbackRegistration<callable(mixed):void>
 */
class SaxCallbackRegistration implements CallbackRegistration
{
    private string $path;

    /**
     * @var callable
     */
    private $callback;

    private XmlNodeTypes $nodeType;

    public function __construct(string $path, callable $callback, XmlNodeTypes $nodeType)
    {
        $this->path = $path;
        $this->callback = $callback;
        $this->nodeType = $nodeType;
    }

    /**
     * @param callable(mixed):void $callback
     */
    public static function create(string $path, $callback, XmlNodeTypes $nodeType): SaxCallbackRegistration
    {
        return new self($path, $callback, $nodeType);
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function asIdString(): string
    {
        return "{$this->nodeType}-{$this->path}";
    }

    public function getCallback(): callable
    {
        return $this->callback;
    }

    public function getNodeType(): XmlNodeTypes
    {
        return $this->nodeType;
    }
}

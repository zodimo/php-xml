<?php

declare(strict_types=1);

namespace Zodimo\Xml;

/**
 * @template Handler
 */
class HandlerRegistrationId
{
    private string $id;

    /**
     * @var class-string<Handler>
     *
     * @phpstan-ignore property.onlyWritten
     */
    private string $handlerClass;

    /**
     * @param class-string<Handler> $handlerClass
     */
    private function __construct(string $id, string $handlerClass)
    {
        $this->id = $id;
        $this->handlerClass = $handlerClass;
    }

    public function __toString()
    {
        return $this->id;
    }

    /**
     * @template _HANDLER
     *
     * @param class-string<_HANDLER> $handlerClass
     *
     * @return HandlerRegistrationId<_HANDLER>
     */
    public static function create(string $id, string $handlerClass): HandlerRegistrationId
    {
        return new self($id, $handlerClass);
    }

    public function getId(): string
    {
        return $this->id;
    }
}

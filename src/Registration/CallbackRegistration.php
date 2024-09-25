<?php

declare(strict_types=1);

namespace Zodimo\Xml\Registration;

use Zodimo\Xml\Enums\XmlNodeTypes;

/**
 * @template CALLBACK is callable
 */
interface CallbackRegistration
{
    /**
     * @template _CALLBACK is callable
     *
     * @param _CALLBACK $callback
     *
     * @return CallbackRegistration<_CALLBACK>
     */
    public static function create(string $path, $callback, XmlNodeTypes $nodeType): CallbackRegistration;

    public function getPath(): string;

    public function asIdString(): string;

    /**
     * @return CALLBACK
     */
    public function getCallback();

    public function getNodeType(): XmlNodeTypes;
}

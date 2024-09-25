<?php

declare(strict_types=1);

namespace Zodimo\Xml\Traits;

use Zodimo\BaseReturn\IOMonad;
use Zodimo\BaseReturn\Tuple;
use Zodimo\Xml\Enums\XmlNodeTypes;
use Zodimo\Xml\Errors\XmlParserException;
use Zodimo\Xml\Parsers\SaxParserInterface;
use Zodimo\Xml\Parsers\XmlParserInterface;
use Zodimo\Xml\Registration\SaxCallbackRegistration;

/**
 * @template ERR
 *
 * @phpstan-require-implements SaxParserInterface<ERR>
 */
trait SaxCallbackInfrastructure
{
    /**
     * @var array<XmlNodeTypes,array<string,string>>
     */
    protected array $callbacks = [];

    /**
     * @var array<string,SaxCallbackRegistration>
     */
    protected array $callbackRegistrations = [];

    /**
     * @param callable(XmlParserInterface<ERR>):bool $callback
     *
     * @return IOMonad<Tuple<SaxCallbackRegistration,XmlParserInterface<ERR>>,XmlParserException>
     */
    public function registerCallback(string $path, callable $callback, XmlNodeTypes $nodeType = XmlNodeTypes::NODE()): IOMonad
    {
        $clone = clone $this;
        if (isset($clone->callbacks[$path])) {
            return IOMonad::fail(XmlParserException::create("Callback on path[{$path}] already exists"));
        }

        $callbackRegistration = SaxCallbackRegistration::create($path, $callback, $nodeType);
        $clone->callbacks[$nodeType][$path] = $callbackRegistration->asIdString();
        $clone->callbackRegistrations[$callbackRegistration->asIdString()] = $callbackRegistration;

        // @phpstan-ignore return.type
        return IOMonad::pure(Tuple::create($callbackRegistration, $clone));
    }

    /**
     * Remove node callback.
     *
     * @param SaxCallbackRegistration $callbackRegistration
     *
     * @return IOMonad<XmlParserInterface<ERR,SaxCallbackRegistration>,XmlParserException>
     */
    public function unRegisterCallback($callbackRegistration): IOMonad
    {
        $path = $callbackRegistration->getPath();
        if (!key_exists($callbackRegistration->asIdString(), $this->callbackRegistrations)) {
            return IOMonad::fail(XmlParserException::create("Callback for path: {$path} does not exist."));
        }
        $clone = clone $this;

        unset($clone->callbackRegistrations[$callbackRegistration->asIdString()], $clone->callbacks[$path]);

        // @phpstan-ignore return.type
        return IOMonad::pure($clone);
    }
}

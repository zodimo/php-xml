<?php

declare(strict_types=1);

namespace Zodimo\Xml\Traits;

use XMLReader;
use Zodimo\BaseReturn\IOMonad;
use Zodimo\BaseReturn\Tuple;
use Zodimo\Xml\Errors\XmlParserException;
use Zodimo\Xml\Parsers\ExiParserInterface;
use Zodimo\Xml\Parsers\XmlParserInterface;
use Zodimo\Xml\Registration\ExiCallbackRegistration;

/**
 * @template ERR
 *
 * @phpstan-require-implements ExiParserInterface<ERR>
 */
trait ExiCallbackInfrastructure
{
    /**
     * @var array<int,array<string,string>>
     */
    protected array $callbacks = [];

    /**
     * @var array<string,ExiCallbackRegistration>
     */
    protected array $callbackRegistrations = [];

    /**
     * @param callable(XmlParserInterface<ERR>):bool $callback
     *
     * @return IOMonad<Tuple<ExiCallbackRegistration,XmlParserInterface<ERR>>,XmlParserException>
     */
    public function registerCallback(string $path, callable $callback, int $nodeType = XMLReader::ELEMENT): IOMonad
    {
        $clone = clone $this;
        if (isset($clone->callbacks[$path])) {
            return IOMonad::fail(XmlParserException::create("Callback on path[{$path}] already exists"));
        }

        $callbackRegistration = ExiCallbackRegistration::create($path, $callback, $nodeType);
        $clone->callbacks[$nodeType][$path] = $callbackRegistration->asIdString();
        $clone->callbackRegistrations[$callbackRegistration->asIdString()] = $callbackRegistration;

        // @phpstan-ignore return.type
        return IOMonad::pure(Tuple::create($callbackRegistration, $clone));
    }

    /**
     * Remove node callback.
     *
     * @param ExiCallbackRegistration $callbackRegistration
     *
     * @return IOMonad<XmlParserInterface<ERR,ExiCallbackRegistration>,XmlParserException>
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

<?php

declare(strict_types=1);

namespace Zodimo\Xml\Traits;

use Zodimo\BaseReturn\IOMonad;
use Zodimo\BaseReturn\Tuple;
use Zodimo\Xml\CallbackRegistration;
use Zodimo\Xml\Errors\XmlParserException;
use Zodimo\Xml\XmlParserInterface;

/**
 * @template ERR
 *
 * @phpstan-require-implements XmlParserInterface<ERR>
 */
trait CallbackInfrastructure
{
    /**
     * @var array<string,string>
     */
    protected array $callbacks = [];

    /**
     * @var array<string,CallbackRegistration>
     */
    protected array $callbackRegistrations = [];

    /**
     * @return IOMonad<Tuple<CallbackRegistration,XmlParserInterface<ERR>>,XmlParserException>
     */
    public function registerCallback(string $path, callable $callback): IOMonad
    {
        $clone = clone $this;
        if (isset($clone->callbacks[$path])) {
            return IOMonad::fail(XmlParserException::create("Callback on path[{$path}] already exists"));
        }

        $callbackRegistration = CallbackRegistration::create($path, $callback);
        $clone->callbacks[$path] = $callbackRegistration->asIdString();
        $clone->callbackRegistrations[$callbackRegistration->asIdString()] = $callbackRegistration;

        // @phpstan-ignore return.type
        return IOMonad::pure(Tuple::create($callbackRegistration, $clone));
    }

    /**
     * Remove node callback.
     *
     * @return IOMonad<XmlParserInterface<ERR>,XmlParserException>
     */
    public function unRegisterCallback(CallbackRegistration $callbackRegistration): IOMonad
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

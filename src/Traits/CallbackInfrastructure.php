<?php

declare(strict_types=1);

namespace Zodimo\Xml\Traits;

use Zodimo\BaseReturn\IOMonad;
use Zodimo\BaseReturn\Tuple;
use Zodimo\Xml\CallbackRegistration;
use Zodimo\Xml\XmlParserInterface;

/**
 * @template ERR
 *
 * @phpstan-require-implements XmlParserInterface<ERR>
 */
trait CallbackInfrastructure
{
    /**
     * @var array<string,callable>
     */
    protected array $callbacks = [];

    /**
     * @var array<string>
     */
    protected array $callbackRegistrations = [];

    /**
     * @return IOMonad<Tuple<CallbackRegistration,XmlParserInterface<ERR>>,string>
     */
    public function registerCallback(string $path, callable $callback): IOMonad
    {
        if ($this->hasHandler($path)) {
            return IOMonad::fail("Callback on path[{$path}] already exists");
        }

        $this->callbacks[$path] = $callback;
        $callbackRegistration = CallbackRegistration::create($path);
        $this->callbackRegistrations[] = $callbackRegistration->asIdString();

        // @phpstan-ignore return.type
        return IOMonad::pure(Tuple::create($callbackRegistration, $this));
    }

    /**
     * Remove node callback.
     *
     * @return IOMonad<XmlParserInterface<ERR>,string>
     */
    public function unRegisterCallback(CallbackRegistration $callbackRegistration): IOMonad
    {
        $path = $callbackRegistration->getPath();
        if (!in_array($callbackRegistration->asIdString(), $this->callbackRegistrations, true)) {
            return IOMonad::fail("Callback for path: {$path} does not exist.");
        }
        unset($this->callbackRegistrations[$callbackRegistration->asIdString()], $this->callbacks[$path]);

        // @phpstan-ignore return.type
        return IOMonad::pure($this);
    }
}

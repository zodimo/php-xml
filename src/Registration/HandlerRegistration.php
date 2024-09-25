<?php

declare(strict_types=1);

namespace Zodimo\Xml\Registration;

/**
 * @template HANDLER of object
 * @template CALLBACKREGISTRATION of CallbackRegistration
 */
class HandlerRegistration
{
    /**
     * @var array<CALLBACKREGISTRATION>
     */
    private array $callbackRegistrations;

    /**
     * @var HANDLER
     */
    private object $handler;

    /**
     * @param HANDLER $handler
     */
    private function __construct($handler)
    {
        $this->handler = $handler;
        $this->callbackRegistrations = [];
    }

    /**
     *  @template _HANDLER of object
     *  @template _CALLBACKREGISTRATION of CallbackRegistration
     *
     * @param array<_CALLBACKREGISTRATION> $callbackRegistrations
     * @param _HANDLER                     $handler
     *
     * @return HandlerRegistration<_HANDLER,_CALLBACKREGISTRATION>
     */
    public static function create(array $callbackRegistrations, object $handler): HandlerRegistration
    {
        $registration = new self($handler);
        foreach ($callbackRegistrations as $callbackRegistration) {
            // php will protect the types
            $registration->addCallbackRegistration($callbackRegistration);
        }

        // @phpstan-ignore return.type
        return $registration;
    }

    /**
     * @return array<CALLBACKREGISTRATION>
     */
    public function getCallbackRegistrations(): array
    {
        return $this->callbackRegistrations;
    }

    /**
     * @return HandlerRegistrationId<HANDLER>
     */
    public function getRegistrationId(): HandlerRegistrationId
    {
        $handlerClass = get_class($this->handler);
        $callbackIdStrings = array_map(function (CallbackRegistration $callbackRegistration) {
            return $callbackRegistration->asIdString();
        }, $this->callbackRegistrations);
        $handlerIdRaw = implode('#', $callbackIdStrings);

        return HandlerRegistrationId::create(hash('sha256', $handlerIdRaw), $handlerClass);
    }

    /**
     * @return HANDLER
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * @param CALLBACKREGISTRATION $callbackRegistration
     */
    private function addCallbackRegistration($callbackRegistration): void
    {
        $this->callbackRegistrations[] = $callbackRegistration;
    }
}

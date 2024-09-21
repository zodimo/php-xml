<?php

declare(strict_types=1);

namespace Zodimo\Xml;

/**
 * @template HANDLER of object
 */
class HandlerRegistration
{
    /**
     * @var array<CallbackRegistration>
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
     *
     * @param array<CallbackRegistration> $callbackRegistrations
     * @param _HANDLER                    $handler
     *
     * @return HandlerRegistration<_HANDLER>
     */
    public static function create(array $callbackRegistrations, object $handler): HandlerRegistration
    {
        $registration = new self($handler);
        foreach ($callbackRegistrations as $callbackRegistration) {
            // php will protect the types
            $registration->addCallbackRegistration($callbackRegistration);
        }

        return $registration;
    }

    /**
     * @return array<CallbackRegistration>
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

    private function addCallbackRegistration(CallbackRegistration $callbackRegistration): void
    {
        $this->callbackRegistrations[] = $callbackRegistration;
    }
}

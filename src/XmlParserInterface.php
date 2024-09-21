<?php

declare(strict_types=1);

namespace Zodimo\Xml;

use Zodimo\BaseReturn\IOMonad;
use Zodimo\BaseReturn\Option;
use Zodimo\BaseReturn\Tuple;

/**
 * @template HANDLERERR
 */
interface XmlParserInterface
{
    /**
     * @template ERR
     * @template HANDLER of CanRegisterWithXmlParser<ERR>
     *
     * @param HANDLER $handler
     *
     * @return IOMonad<Tuple<HandlerRegistrationId<HANDLER>,XmlParserInterface<ERR>>,ERR>
     */
    public function registerHandler(CanRegisterWithXmlParser $handler): IOMonad;

    /**
     * If handler not found, then just return the parser unchanged.
     * or should it ?
     *
     * @template HANDLER
     *
     * @param HandlerRegistrationId<HANDLER> $handlerRegistrationId
     *
     * @return IOMonad<XmlParserInterface<HANDLERERR>,mixed>
     */
    public function unRegisterHandlerByRegisterationId(HandlerRegistrationId $handlerRegistrationId): IOMonad;

    /**
     * @return IOMonad<Tuple<CallbackRegistration,XmlParserInterface<HANDLERERR>>,string>
     */
    public function registerCallback(string $path, callable $callback): IOMonad;

    /**
     * Remove node callback.
     *
     * @return IOMonad<SaxParser,string>
     */
    public function unRegisterCallback(CallbackRegistration $callbackRegistration): IOMonad;

    /**
     * Support xml and xml.gz.
     *
     * @return IOMonad<HasHandlers,mixed>
     */
    public function parseFile(string $file): IOMonad;

    /**
     * @return IOMonad<HasHandlers,mixed>
     */
    public function parseString(string $data, bool $isFinal): IOMonad;

    /**
     * @template HANDLER
     *
     * @param HandlerRegistrationId<HANDLER> $id
     *
     * @return Option<HANDLER>
     */
    public function getHandlerById(HandlerRegistrationId $id): Option;

    /**
     * @template ERR of \Throwable
     * @template HANDLER of CanRegisterWithXmlParser<ERR>
     *
     * @param HandlerRegistration<HANDLER> $handlerRegistration
     *
     * @return IOMonad<XmlParserInterface<ERR>,mixed>
     */
    public function addHandlerRegistration(HandlerRegistration $handlerRegistration): IOMonad;

    /**
     * @template ERR of \Throwable
     * @template HANDLER of CanRegisterWithXmlParser<ERR>
     *
     * @param HandlerRegistrationId<HANDLER> $id
     *
     * @return IOMonad<XmlParserInterface<ERR>,mixed>
     */
    public function removeHandlerById(HandlerRegistrationId $id): IOMonad;
}

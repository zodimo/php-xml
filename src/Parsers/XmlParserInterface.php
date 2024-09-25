<?php

declare(strict_types=1);

namespace Zodimo\Xml\Parsers;

use Zodimo\BaseReturn\IOMonad;
use Zodimo\BaseReturn\Option;
use Zodimo\BaseReturn\Tuple;
use Zodimo\Xml\Errors\XmlParserException;
use Zodimo\Xml\Errors\XmlParsingException;
use Zodimo\Xml\Registration\CallbackRegistration;
use Zodimo\Xml\Registration\HandlerRegistration;
use Zodimo\Xml\Registration\HandlerRegistrationId;

/**
 * @template HANDLERERR
 * @template CALLBACKREGISTRATION of CallbackRegistration
 */
interface XmlParserInterface
{
    /**
     * @template ERR
     * @template HANDLER of CanRegisterWithXmlParser<ERR>
     *
     * @param HANDLER $handler
     *
     * @return IOMonad<Tuple<HandlerRegistrationId<HANDLER>,XmlParserInterface<ERR,CALLBACKREGISTRATION>>,ERR>
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
     * @return IOMonad<XmlParserInterface<HANDLERERR,CALLBACKREGISTRATION>,XmlParserException>
     */
    public function unRegisterHandlerByRegisterationId(HandlerRegistrationId $handlerRegistrationId): IOMonad;

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
     * @param HandlerRegistration<HANDLER,CALLBACKREGISTRATION> $handlerRegistration
     *
     * @return IOMonad<XmlParserInterface<ERR,CALLBACKREGISTRATION>,XmlParserException>
     */
    public function addHandlerRegistration(HandlerRegistration $handlerRegistration): IOMonad;

    /**
     * @template ERR
     * @template HANDLER of CanRegisterWithXmlParser<ERR>
     *
     * @param HandlerRegistrationId<HANDLER> $id
     *
     * @return IOMonad<XmlParserInterface<ERR,CALLBACKREGISTRATION>,XmlParserException>
     */
    public function removeHandlerById(HandlerRegistrationId $id): IOMonad;

    /**
     * might need meer detailed types about callback.
     *
     * @return IOMonad<Tuple<CALLBACKREGISTRATION,XmlParserInterface<HANDLERERR,CALLBACKREGISTRATION>>,XmlParserException>
     */
    public function registerCallback(string $path, callable $callback): IOMonad;

    /**
     * Remove node callback.
     *
     * @param CALLBACKREGISTRATION $callbackRegistration
     *
     * @return IOMonad<XmlParserInterface<HANDLERERR,CALLBACKREGISTRATION>,XmlParserException>
     */
    public function unRegisterCallback($callbackRegistration): IOMonad;

    /**
     * Support xml and xml.gz.
     *
     * @return IOMonad<HasHandlers,XmlParsingException>
     */
    public function parseFile(string $file): IOMonad;

    /**
     * @return IOMonad<HasHandlers,XmlParsingException>
     */
    public function parseString(string $data, bool $isFinal): IOMonad;
}

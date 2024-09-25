<?php

declare(strict_types=1);

namespace Zodimo\Xml\Parsers;

use Zodimo\BaseReturn\IOMonad;
use Zodimo\BaseReturn\Tuple;
use Zodimo\Xml\Registration\CallbackRegistration;
use Zodimo\Xml\Registration\HandlerRegistration;

/**
 * @template ERR
 */
interface CanRegisterWithXmlParser
{
    /**
     * @template PARSERERR
     * @template CALLBACKREGISTRATION of CallbackRegistration
     *
     * @param XmlParserInterface<PARSERERR,CALLBACKREGISTRATION> $parser
     *
     * @return IOMonad<Tuple<HandlerRegistration<self<ERR>,CALLBACKREGISTRATION>,XmlParserInterface<ERR|PARSERERR,CALLBACKREGISTRATION>>,PARSERERR>
     */
    public function registerWithParser(XmlParserInterface $parser): IOMonad;
}

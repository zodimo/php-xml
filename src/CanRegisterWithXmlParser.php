<?php

declare(strict_types=1);

namespace Zodimo\Xml;

use Zodimo\BaseReturn\IOMonad;
use Zodimo\BaseReturn\Tuple;

/**
 * @template ERR
 */
interface CanRegisterWithXmlParser
{
    /**
     * @template PARSERERR
     *
     * @param XmlParserInterface<PARSERERR> $parser
     *
     * @return IOMonad<Tuple<HandlerRegistration<self<ERR>>,XmlParserInterface<ERR|PARSERERR>>,PARSERERR>
     */
    public function registerWithParser(XmlParserInterface $parser): IOMonad;
}

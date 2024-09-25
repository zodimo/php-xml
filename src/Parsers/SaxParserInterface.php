<?php

declare(strict_types=1);

namespace Zodimo\Xml\Parsers;

use Zodimo\Xml\Registration\SaxCallbackRegistration;

/**
 * @template HANDLERERR
 *
 * @extends XmlParserInterface<HANDLERERR,SaxCallbackRegistration>
 */
interface SaxParserInterface extends XmlParserInterface {}

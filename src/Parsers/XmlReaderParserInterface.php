<?php

declare(strict_types=1);

namespace Zodimo\Xml\Parsers;

use Zodimo\Xml\Registration\XmlReaderCallbackRegistration;

/**
 * @template HANDLERERR
 *
 * @extends XmlParserInterface<HANDLERERR,XmlReaderCallbackRegistration>
 */
interface XmlReaderParserInterface extends XmlParserInterface {}

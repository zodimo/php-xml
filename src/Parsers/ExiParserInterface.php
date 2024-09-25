<?php

declare(strict_types=1);

namespace Zodimo\Xml\Parsers;

use Zodimo\Xml\Registration\ExiCallbackRegistration;

/**
 * @template HANDLERERR
 *
 * @extends XmlParserInterface<HANDLERERR,ExiCallbackRegistration>
 */
interface ExiParserInterface extends XmlParserInterface {}

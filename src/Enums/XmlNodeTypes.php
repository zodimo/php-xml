<?php

declare(strict_types=1);

namespace Zodimo\Xml\Enums;

use MyCLabs\Enum\Enum;

/**
 * @method static XmlNodeTypes COMMENT()
 * @method static XmlNodeTypes TEXT()
 * @method static XmlNodeTypes PI()
 * @method static XmlNodeTypes NODE()
 *
 * @extends Enum<string>
 */
class XmlNodeTypes extends Enum
{
    // @phpstan-ignore classConstant.unused
    private const COMMENT = 'comment';
    // @phpstan-ignore classConstant.unused
    private const TEXT = 'comment';
    // @phpstan-ignore classConstant.unused
    private const PI = 'processing-instruction';
    // @phpstan-ignore classConstant.unused
    private const NODE = 'node';
}

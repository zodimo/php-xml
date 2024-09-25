<?php

declare(strict_types=1);

namespace Zodimo\Xml\Parsers;

use Zodimo\BaseReturn\Option;
use Zodimo\Xml\Registration\HandlerRegistrationId;

interface HasHandlers
{
    /**
     * @template HANDLER
     *
     * @param HandlerRegistrationId<HANDLER> $id
     *
     * @return Option<HANDLER>
     */
    public function getHandlerById(HandlerRegistrationId $id): Option;
}

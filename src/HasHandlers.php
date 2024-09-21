<?php

declare(strict_types=1);

namespace Zodimo\Xml;

use Zodimo\BaseReturn\Option;

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

<?php

declare(strict_types=1);

namespace Zodimo\Xml;

interface XmlReaderHandlerInterface
{
    public function registerCallbacks(RegisterListenerInterface $parser): void;
}

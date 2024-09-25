<?php

declare(strict_types=1);

namespace Zodimo\Xml\Traits;

use Zodimo\BaseReturn\IOMonad;
use Zodimo\BaseReturn\Option;
use Zodimo\BaseReturn\Tuple;
use Zodimo\Xml\Errors\XmlParserException;
use Zodimo\Xml\Parsers\CanRegisterWithXmlParser;
use Zodimo\Xml\Parsers\XmlParserInterface;
use Zodimo\Xml\Registration\HandlerRegistration;
use Zodimo\Xml\Registration\HandlerRegistrationId;

/**
 * @template ERR
 *
 * @phpstan-require-implements XmlParserInterface<ERR>
 */
trait HandlerInfrastructure
{
    /**
     * @var array<string,HandlerRegistration>
     */
    protected array $handlers = [];

    /**
     * @template _ERR
     * @template HANDLER of CanRegisterWithXmlParser<_ERR>
     *
     * @param HANDLER $handler
     *
     * @return IOMonad<Tuple<HandlerRegistrationId<HANDLER>,XmlParserInterface<ERR>>,_ERR|XmlParserException>
     */
    public function registerHandler(CanRegisterWithXmlParser $handler): IOMonad
    {
        $handlerClone = clone $handler;
        $parserClone = clone $this;

        return $handlerClone
            ->registerWithParser($parserClone)
            ->flatMap(function (Tuple $input) {
                $handlerRegistration = $input->fst();
                $parser = $input->snd();

                if (!$handlerRegistration instanceof HandlerRegistration) {
                    return IOMonad::fail(XmlParserException::create('Expected HandlerRegistration in Tuple::FIRST'));
                }

                if (!$parser instanceof XmlParserInterface) {
                    return IOMonad::fail(XmlParserException::create('Expected XmlParserInterface in Tuple::SECOND'));
                }

                return $parser
                    ->addHandlerRegistration($handlerRegistration)
                    ->fmap(
                        fn ($p) => Tuple::create($handlerRegistrationId, $p)
                    )
                ;
            })
        ;
    }

    /**
     * @template HANDLER
     *
     * @param HandlerRegistrationId<HANDLER> $handlerRegistrationId
     *
     * @return IOMonad<XmlParserInterface,XmlParserException>
     */
    public function unRegisterHandlerByRegisterationId(HandlerRegistrationId $handlerRegistrationId): IOMonad
    {
        $parser = clone $this;

        return $this->getHandlerRegistrationById($handlerRegistrationId)->match(
            function ($registration) use ($parser, $handlerRegistrationId) {
                $parserResult = IOMonad::pure($parser);
                foreach ($registration->getCallbackRegistrations() as $callbackRegistration) {
                    $parserResult = $parserResult->flatMap(fn ($parser) => $parser->unRegisterCallback($callbackRegistration));
                }

                return $parserResult->flatMap(function ($parser) use ($handlerRegistrationId) {
                    return $parser->removeHandlerById($handlerRegistrationId);
                });
            },
            fn () => IOMonad::fail(
                XmlParserException::create('Handler does not exist')
            )
        );
    }

    /**
     * @template HANDLER
     *
     * @param HandlerRegistrationId<HANDLER> $id
     *
     * @return Option<HANDLER>
     */
    public function getHandlerById(HandlerRegistrationId $id): Option
    {
        if (isset($this->handlers[$id->getId()])) {
            $handlerRegistration = $this->handlers[$id->getId()];

            return Option::some($handlerRegistration->getHandler());
        }

        return Option::none();
    }

    /**
     * @template HANDLER of object
     *
     * @param HandlerRegistration<HANDLER> $handlerRegistration
     *
     * @return IOMonad<XmlParserInterface,XmlParserException>
     */
    public function addHandlerRegistration(HandlerRegistration $handlerRegistration): IOMonad
    {
        $handlerIdAsString = $handlerRegistration->getRegistrationId()->getId();
        if (isset($this->handlers[$handlerIdAsString])) {
            return IOMonad::fail(XmlParserException::create('Handler already registered'));
        }
        $clone = clone $this;

        $clone->handlers[$handlerIdAsString] = $handlerRegistration;

        return IOMonad::pure($clone);
    }

    /**
     * @template HANDLER
     *
     * @param HandlerRegistrationId<HANDLER> $id
     *
     * @return IOMonad<XmlParserInterface,XmlParserException>
     */
    public function removeHandlerById(HandlerRegistrationId $id): IOMonad
    {
        $handlerIdAsString = $id->getId();
        if (!isset($this->handlers[$handlerIdAsString])) {
            return IOMonad::fail(XmlParserException::create('Handler with id does not exists'));
        }
        $clone = clone $this;
        unset($clone->handlers[$handlerIdAsString]);

        return IOMonad::pure($clone);
    }
}

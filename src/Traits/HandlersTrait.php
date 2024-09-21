<?php

declare(strict_types=1);

namespace Zodimo\Xml\Traits;

use Zodimo\BaseReturn\IOMonad;
use Zodimo\BaseReturn\Option;
use Zodimo\BaseReturn\Tuple;
use Zodimo\Xml\CallbackRegistration;
use Zodimo\Xml\CanRegisterWithXmlParser;
use Zodimo\Xml\HandlerRegistration;
use Zodimo\Xml\HandlerRegistrationId;
use Zodimo\Xml\XmlParserInterface;

/**
 * @template ERR
 *
 * @phpstan-require-implements XmlParserInterface<ERR>
 */
trait HandlersTrait
{
    /**
     * @var array<string,callable>
     */
    protected array $callbacks = [];

    /**
     * @var array<string>
     */
    protected array $callbackRegistration = [];

    /**
     * @var array<string,HandlerRegistration>
     */
    protected array $handlers = [];

    /**
     * @return IOMonad<Tuple<CallbackRegistration,XmlParserInterface>,string>
     */
    public function registerCallback(string $path, callable $callback): IOMonad
    {
        if ($this->hasHandler($path)) {
            return IOMonad::fail("Callback on path[{$path}] already exists");
        }

        $this->callbacks[$path] = $callback;
        $callbackRegistration = CallbackRegistration::create($path);
        $this->callbackRegistrations[] = $callbackRegistration->asIdString();

        // @phpstan-ignore return.type
        return IOMonad::pure(Tuple::create($callbackRegistration, $this));
    }

    /**
     * Remove node callback.
     *
     * @return IOMonad<XmlParserInterface,string>
     */
    public function unRegisterCallback(CallbackRegistration $callbackRegistration): IOMonad
    {
        $path = $callbackRegistration->getPath();
        if (!in_array($callbackRegistration->asIdString(), $this->callbackRegistrations, true)) {
            return IOMonad::fail("Callback for path: {$path} does not exist.");
        }
        unset($this->callbackRegistrations[$callbackRegistration->asIdString()], $this->callbacks[$path]);

        // @phpstan-ignore return.type
        return IOMonad::pure($this);
    }

    /**
     * @template _ERR
     * @template HANDLER of CanRegisterWithXmlParser<_ERR>
     *
     * @param HANDLER $handler
     *
     * @return IOMonad<Tuple<HandlerRegistrationId<HANDLER>,XmlParserInterface<ERR>>,_ERR|\RuntimeException>
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
                    return IOMonad::fail(new \RuntimeException('Expected HandlerRegistration in Tuple::FIRST'));
                }

                if (!$parser instanceof XmlParserInterface) {
                    return IOMonad::fail(new \RuntimeException('Expected XmlParserInterface in Tuple::SECOND'));
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
     * If handler not found, then just return the parser unchanged.
     * or should it ?
     *
     * @template HANDLER
     *
     * @param HandlerRegistrationId<HANDLER> $handlerRegistrationId
     *
     * @return IOMonad<XmlParserInterface,mixed>
     */
    public function unRegisterHandlerByRegisterationId(HandlerRegistrationId $handlerRegistrationId): IOMonad
    {
        $parser = $this;

        // @phpstan-ignore return.type
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
            fn () => IOMonad::pure($this)
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
     * @return IOMonad<XmlParserInterface,mixed>
     */
    public function addHandlerRegistration(HandlerRegistration $handlerRegistration): IOMonad
    {
        $handlerIdAsString = $handlerRegistration->getRegistrationId()->getId();
        if (isset($this->handlers[$handlerIdAsString])) {
            return IOMonad::fail('Handler already registered');
        }
        $this->handlers[$handlerIdAsString] = $handlerRegistration;

        return IOMonad::pure($this);
    }

    /**
     * @template HANDLER
     *
     * @param HandlerRegistrationId<HANDLER> $id
     *
     * @return IOMonad<XmlParserInterface,mixed>
     */
    public function removeHandlerById(HandlerRegistrationId $id): IOMonad
    {
        $handlerIdAsString = $id->getId();
        if (!isset($this->handlers[$handlerIdAsString])) {
            return IOMonad::fail('Handler with id does not exists');
        }
        unset($this->handlers[$handlerIdAsString]);

        return IOMonad::pure($this);
    }
}

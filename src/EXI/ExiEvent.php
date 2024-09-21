<?php

declare(strict_types=1);

namespace Zodimo\Xml\EXI;

/**
 * @see https://www.w3.org/TR/2014/WD-exi-primer-20140424/
 */
class ExiEvent
{
    public const TAG_START_DOCUMENT = 'SD';
    public const TAG_END_DOCUMENT = 'ED';
    public const TAG_START_ELEMENT = 'SE';
    public const TAG_END_ELEMENT = 'EE';
    public const TAG_ATTRIBUTE = 'AT';
    public const TAG_CHARACTERS = 'CH';
    public const TAG_NAMESPACE_DECLARATION = 'NS';
    public const TAG_COMMENT = 'CM';
    public const TAG_PROCESS_INSTRUCTION = 'PI';
    public const TAG_DOC_TYPE = 'DT';
    public const TAG_ENTITY_REFERENCE = 'ER';
    public const TAG_SELF_CONTAINED = 'SC';

    private string $grammerNotation;

    /**
     * @var array<string,mixed>
     */
    private array $parameters;

    /**
     * @param array<string,mixed> $parameters
     */
    private function __construct(string $grammerNotation, array $parameters = [])
    {
        $this->grammerNotation = $grammerNotation;
        $this->parameters = $parameters;
    }

    public static function startDocument(): ExiEvent
    {
        return new self(ExiEvent::TAG_START_DOCUMENT);
    }

    public static function endDocument(): ExiEvent
    {
        return new self(ExiEvent::TAG_END_DOCUMENT);
    }

    public static function startElement(string $elementName): ExiEvent
    {
        return new self(
            ExiEvent::TAG_START_ELEMENT,
            [
                'elementName' => $elementName,
                'subType' => '*',
            ]
        );
    }

    public static function startElementQName(string $elementName): ExiEvent
    {
        return new self(
            ExiEvent::TAG_START_ELEMENT,
            [
                'elementName' => $elementName,
                'subType' => 'qname',
            ]
        );
    }

    public static function startElementUri(string $elementName): ExiEvent
    {
        return new self(
            ExiEvent::TAG_START_ELEMENT,
            [
                'elementName' => $elementName,
                'subType' => 'uri:*',
            ]
        );
    }

    public static function endElement(): ExiEvent
    {
        return new self(ExiEvent::TAG_END_ELEMENT);
    }

    /**
     * @param mixed $attributeValue
     */
    public static function attribute(string $attributeName, $attributeValue): ExiEvent
    {
        return new self(
            ExiEvent::TAG_ATTRIBUTE,
            [
                'attributeName' => $attributeName,
                'attributeValue' => $attributeValue,
                'subType' => '*',
            ]
        );
    }

    /**
     * @param mixed $attributeValue
     */
    public static function attributeQName(string $attributeName, $attributeValue): ExiEvent
    {
        return new self(
            ExiEvent::TAG_ATTRIBUTE,
            [
                'attributeName' => $attributeName,
                'attributeValue' => $attributeValue,
                'subType' => 'qname',
            ]
        );
    }

    /**
     * @param mixed $attributeValue
     */
    public static function attributeUri(string $attributeName, $attributeValue): ExiEvent
    {
        return new self(
            ExiEvent::TAG_ATTRIBUTE,
            [
                'attributeName' => $attributeName,
                'attributeValue' => $attributeValue,
                'subType' => 'uri:*',
            ]
        );
    }

    public static function characters(string $data): ExiEvent
    {
        return new self(
            ExiEvent::TAG_CHARACTERS,
            [
                'characters' => $data,
            ]
        );
    }

    public static function namespaceDeclaration(): ExiEvent
    {
        return new self(ExiEvent::TAG_NAMESPACE_DECLARATION);
    }

    public static function comment(): ExiEvent
    {
        return new self(ExiEvent::TAG_COMMENT);
    }

    public static function processInstruction(): ExiEvent
    {
        return new self(ExiEvent::TAG_PROCESS_INSTRUCTION);
    }

    public static function docType(): ExiEvent
    {
        return new self(ExiEvent::TAG_DOC_TYPE);
    }

    public static function entityReference(): ExiEvent
    {
        return new self(ExiEvent::TAG_ENTITY_REFERENCE);
    }

    public static function selfContained(): ExiEvent
    {
        return new self(ExiEvent::TAG_SELF_CONTAINED);
    }

    public function getGrammerNotation(): string
    {
        return $this->grammerNotation;
    }

    /**
     * @return array<string,mixed>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }
}

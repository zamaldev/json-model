<?php

declare(strict_types=1);

namespace Tests\Model;

use Attribute;
use Zamaldev\JsonModel\Attributes\Caster;
use Zamaldev\JsonModel\JsonModelInterface;

#[Attribute(Attribute::TARGET_PROPERTY)]
class SimpleCast implements Caster
{
    public const TYPE_STRING = 'union';
    public const TYPE_CLASS = 'intersection';
    public const TYPE_ARRAY = 'array';

    public function __construct(
        private string $result,
    ) {}

    /**
     * @inheritDoc
     */
    public function cast(JsonModelInterface $jsonModel, mixed $value): mixed
    {
        return match ($this->result) {
            self::TYPE_STRING => 'cast',
            self::TYPE_CLASS => new class() implements Interface1, Interface2 {
                public $text = 'cast';
            },
            self::TYPE_ARRAY => match ($value) {
                'string' => 'string',
                'int' => 5,
                'bool' => true,
                'float' => 3.14,
                'object' => new class() implements Interface1, Interface2 {
                    public $text = 'cast';
                },
            },
        };
    }
}

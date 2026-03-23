<?php

declare(strict_types=1);

namespace Tests\Model;

use Zamaldev\JsonModel\Attributes\AsArray;
use Zamaldev\JsonModel\Attributes\Map;
use Zamaldev\JsonModel\JsonModel;

class Model1 extends JsonModel
{
    public string $string;

    public int $int;

    public bool $bool;

    public float $float;

    #[Map(name: 'null_str')]
    public ?string $nullableString = null;

    public array $array;

    #[AsArray(itemType: 'int')]
    public array $arrayOfNumbers;

    #[AsArray(itemType: Model2::class)]
    public array $arrayOfObjects;
}

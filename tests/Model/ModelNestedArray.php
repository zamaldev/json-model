<?php

declare(strict_types=1);

namespace Tests\Model;

use Zamaldev\JsonModel\Attributes\AsArray;

class ModelNestedArray
{
    /**
     * @var array $array
     */
    #[AsArray(itemType: 'int', level: 100)]
    public array $data = [];
}

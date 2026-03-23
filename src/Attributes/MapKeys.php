<?php

declare(strict_types=1);

namespace Zamaldev\JsonModel\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class MapKeys
{
    public function __construct(
        public string $mapper,
    ) {
    }
}

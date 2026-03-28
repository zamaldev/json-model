<?php

declare(strict_types=1);

namespace Zamaldev\JsonModel\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class AsArray
{
    public function __construct(
        public ?string $itemType,
    ) {
    }
}

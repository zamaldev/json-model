<?php

declare(strict_types=1);

namespace Zamaldev\JsonModel\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
interface Sanitizer
{
    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public function sanitize(mixed $value): mixed;
}

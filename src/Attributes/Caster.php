<?php

declare(strict_types=1);

namespace Zamaldev\JsonModel\Attributes;

use Attribute;
use Zamaldev\JsonModel\JsonModelInterface;

#[Attribute(Attribute::TARGET_PROPERTY)]
interface Caster
{
    /**
     * @param JsonModelInterface $jsonModel
     * @param mixed $value
     *
     * @return mixed
     */
    public function cast(JsonModelInterface $jsonModel, mixed $value): mixed;
}

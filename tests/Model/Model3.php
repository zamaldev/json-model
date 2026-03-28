<?php

declare(strict_types=1);

namespace Tests\Model;

use Zamaldev\JsonModel\Attributes\AsArray;

class Model3
{
    #[SimpleCast(SimpleCast::TYPE_STRING)]
    public $untyped;

    #[SimpleCast(SimpleCast::TYPE_STRING)]
    public mixed $mixed;

    #[SimpleCast(SimpleCast::TYPE_STRING)]
    public Model1|int|array|string $union;

    #[SimpleCast(SimpleCast::TYPE_CLASS)]
    public Interface1&Interface2 $intersect;

    #[SimpleCast(SimpleCast::TYPE_STRING)]
    public (Interface1&Interface2)|string $dnf1;

    #[SimpleCast(SimpleCast::TYPE_CLASS)]
    public (Interface1&Interface2)|string $dnf2;

    #[AsArray()]
    #[SimpleCast(SimpleCast::TYPE_ARRAY)]
    public array $array = [];
}

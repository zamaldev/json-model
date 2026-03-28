<?php

declare(strict_types=1);

namespace Zamaldev\JsonModel\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class SimpleSanitizer implements Sanitizer
{
    public function __construct(
        /**
         * Untyped because class properties could not has callable type. But it is actually callable
         *
         * @see https://wiki.php.net/rfc/typed_properties_v2#supported_types
         */
        private $callable
    ) {
    }

    /**
     * @inheritDoc
     */
    public function sanitize(mixed $value): mixed
    {
        return call_user_func($this->callable, $value);
    }
}

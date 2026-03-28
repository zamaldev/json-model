<?php

declare(strict_types=1);

namespace Zamaldev\JsonModel;

use stdClass;

interface JsonModelInterface
{
    /**
     * Parse json from string, array, \stdClass.
     *
     * @param string|array|stdClass $json
     * @param class-string<T>|T $object
     *
     * @return T
     *
     * @template T
     */
    public function parse(string|array|stdClass $json, string|object $object): object;
}

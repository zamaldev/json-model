<?php

declare(strict_types=1);

namespace Zamaldev\JsonModel;

use Closure;
use Exception;
use ReflectionClass;
use stdClass;
use Zamaldev\JsonModel\Attributes\AsArray;
use Zamaldev\JsonModel\Attributes\Map;
use Zamaldev\JsonModel\Attributes\MapKeys;

class JsonModel
{
    /**
     * Model's structure cache.
     *
     * @var array<class-string,array{properties:array{name:string,key:string,type:\Closure,is_array:bool}}>
     */
    private static array $meta = [];

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
    public function parse(string|array|stdClass $json, string|object $object): object
    {
        if (is_string($object)) {
            $object = new $object();
        }

        try {
            if (is_string($json)) {
                $json = json_decode($json);
            } elseif (is_array($json)) {
                $json = json_decode(json_encode($json));
            }
            if (!($json instanceof stdClass)) {
                return $object;
            }
        } catch (Exception $e) {
            return $object;
        }

        return $this->parseFromStdClass($json, $object);
    }

    /**
     * Parse \stdClass to special json model.
     *
     * @param stdClass $json
     * @param object $object
     *
     * @return object
     */
    private function parseFromStdClass(stdClass $json, object $object): object
    {
        $meta = $this->getMeta($object);

        foreach ($meta['properties'] as $property) {
            $key = $property['key'];
            if (!property_exists($json, $key)) {
                continue;
            }

            $name = $property['name'];
            $type = $property['type'];

            $tmpValue = $json->{$key};

            if (!$property['is_array']) {
                $object->{$name} = $type($tmpValue);

                continue;
            }

            $res = [];
            foreach ($tmpValue as $value) {
                if (null === $value) {
                    continue;
                }

                $res[] = $type($value);
            }

            $object->{$name} = $res;
        }

        return $object;
    }

    /**
     * @param object $object
     *
     * @return array{properties:array{name:string,key:string,type:\Closure,is_array:bool}}
     */
    private function getMeta(object $object): array
    {
        $class = $object::class;
        if (isset(self::$meta[$class])) {
            return self::$meta[$class];
        }

        $reflection = new ReflectionClass($object);
        $keyMapper  = null;
        foreach ($reflection->getAttributes(MapKeys::class) as $attribute) {
            $arguments = $attribute->getArguments();
            $className = array_shift($arguments);
            $keyMapper = new $className(...$arguments);
        }

        $properties = [];
        foreach ($reflection->getProperties() as $property) {
            $key = $name = $property->getName();
            if (null !== $keyMapper) {
                $key = $keyMapper->map($key);
            }
            foreach ($property->getAttributes(Map::class) as $attribute) {
                $key = $attribute->getArguments()['name'] ?? $key;
            }

            $tmpType = $property->getType()->getName();

            if ('array' !== $tmpType) {
                $properties[] = [
                    'name' => $name,
                    'key' => $key,
                    'type' => $this->getTypeCallback($tmpType),
                    'is_array' => false,
                ];

                continue;
            }

            // From here property is an array.

            $arrayType = 'string';
            foreach ($property->getAttributes(AsArray::class) as $attribute) {
                $arrayType = $attribute->getArguments()['itemType'] ?? $arrayType;
            }

            $properties[] = [
                'name' => $name,
                'key' => $key,
                'type' => $this->getTypeCallback($arrayType),
                'is_array' => true,
            ];
        }

        $meta = [
            'properties' => $properties,
        ];

        self::$meta[$class] = $meta;

        return $meta;
    }

    /**
     * Convert item to any type except array, and object.
     *
     * @param string $type
     *
     * @return Closure
     */
    private function getTypeCallback(string $type): Closure
    {
        switch ($type) {
            case 'string':
                return static fn(mixed $item) => (string) $item;
            case 'int':
            case 'integer':
                return static fn(mixed $item) => (int) $item;
            case 'float':
            case 'double':
                return static fn(mixed $item) => (float) $item;
            case 'bool':
                return static fn(mixed $item) => boolval($item);
            case 'mixed':
                return static fn(mixed $item) => $item;
        }

        return fn(mixed $item) => ($this->parseFromStdClass($item, new $type));
    }
}

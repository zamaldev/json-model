<?php

declare(strict_types=1);

namespace Zamaldev\JsonModel;

use Closure;
use Exception;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionNamedType;
use stdClass;
use Zamaldev\JsonModel\Attributes\AsArray;
use Zamaldev\JsonModel\Attributes\Caster;
use Zamaldev\JsonModel\Attributes\Map;
use Zamaldev\JsonModel\Attributes\MapKeys;
use Zamaldev\JsonModel\Attributes\Sanitizer;

class JsonModel implements JsonModelInterface
{
    /**
     * Model's structure cache.
     *
     * @var array<class-string,array{array{name:string,key:string,type:\Closure,sanitizers:array<Sanitizer>,array_level:int}}>
     */
    protected static array $meta = [];

    /**
     * @inheritDoc
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
    protected function parseFromStdClass(stdClass $json, object $object): object
    {
        $meta = $this->getMeta($object);

        foreach ($meta as $property) {
            $key = $property['key'];
            if (!property_exists($json, $key)) {
                continue;
            }

            $name = $property['name'];
            $type = $property['type'];

            $tmpValue = $json->{$key};

            if (!$property['array_level']) {
                foreach ($property['sanitizers'] as $sanitizer) {
                    $tmpValue = $sanitizer->sanitize($tmpValue);
                }
                $object->{$name} = $type($tmpValue);

                continue;
            }

            $object->{$name} = $this->getArrayValues($tmpValue, $property['sanitizers'], $type, $property['array_level']);
        }

        return $object;
    }

    /**
     * @param array $tmpValue
     * @param array $sanitizers
     * @param Closure $type
     * @param int $arrayLevel
     *
     * @return array
     */
    protected function getArrayValues(array $tmpValue, array $sanitizers, Closure $type, int $arrayLevel): array
    {
        $res = [];
        if (1 < $arrayLevel) {
            return array_map(fn ($value) => $this->getArrayValues($value, $sanitizers, $type, $arrayLevel - 1), $tmpValue);
        }
        $res = [];
        foreach ($tmpValue as $value) {
            foreach ($sanitizers as $sanitizer) {
                $value = $sanitizer->sanitize($value);
            }

            $res[] = $type($value);
        }

        return $res;
    }

    /**
     * @param object $object
     *
     * @return array{array{name:string,key:string,type:\Closure,sanitizers:array<Sanitizer>,array_level:int}}
     */
    protected function getMeta(object $object): array
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

            $type = $property->getType();
            $tmpType = null;
            $castType = null;
            if ($type instanceof ReflectionNamedType) {
                $tmpType = $property->getType()->getName();
            }
            foreach ($property->getAttributes(Caster::class, ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
                $castType = $attribute->newInstance();
            }
            if (null === $tmpType && null === $castType) {
                throw new Exception("Cannot found type of {$name}. It must have named type or has attribute that implements Caster interface.");
            }

            $sanitizers = [];
            foreach ($property->getAttributes(Sanitizer::class, ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
                $sanitizers[] = $attribute->newInstance();
            }

            if ('array' !== $tmpType) {
                $properties[] = [
                    'name' => $name,
                    'key' => $key,
                    'type' => $this->getTypeCallback($castType ?? $tmpType),
                    'sanitizers' => $sanitizers,
                    'array_level' => 0,
                ];

                continue;
            }

            // From here property is an array.

            $arrayType = 'string';
            $arrayLevel = 1;
            foreach ($property->getAttributes(AsArray::class) as $attribute) {
                $arguments = $attribute->getArguments();
                $arrayType = $arguments['itemType'] ?? $castType ?? $arrayType;
                $arrayLevel = $arguments['level'] ?? $arrayLevel;
            }

            $properties[] = [
                'name' => $name,
                'key' => $key,
                'type' => $this->getTypeCallback($arrayType),
                'sanitizers' => $sanitizers,
                'array_level' => $arrayLevel,
            ];
        }

        self::$meta[$class] = $properties;

        return $properties;
    }

    /**
     * Convert item to any type except array, and object.
     *
     * @param string|Caster $type
     *
     * @return Closure
     */
    protected function getTypeCallback(string|Caster $type): Closure
    {
        if ($type instanceof Caster) {
            return fn(mixed $item) => $type->cast($this, $item);
        }

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

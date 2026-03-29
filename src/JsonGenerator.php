<?php

declare(strict_types=1);

namespace Zamaldev\JsonModel;

use Exception;
use stdClass;

class JsonGenerator
{
    private const KEY_TYPE         = 'type';
    private const KEY_ARRAY_LEVEL  = 'array_level';
    private const KEY_SUBSTRUCTURE = 'substructure';
    private const STUBS_PATH       = __DIR__ . '/../stubs';
    /**
     * @see https://www.php.net/manual/en/language.variables.basics.php
     */
    private const PROPERTY_NAME_REGEX = '^[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*$';
    private const PROPERTY_INVALID_PREFIX = '^[^a-zA-Z_\x80-\xff]+';
    private const PROPERTY_INVALID_SUFFIX = '[^a-zA-Z0-9_\x80-\xff]';

    /**
     * @var array<string,array>
     */
    private array $generatedClasses = [];
    private string $namespace = '';
    private string $rootPath = '';
    private string $rootModel = '';
    private bool $strictTypes = true;
    private bool $overrideExists = false;
    private bool $normalizeArray = true;

    /**
     * Set models namespace.
     *
     * @param string $namespace
     *
     * @return self
     */
    public function namespace(string $namespace): self
    {
        $this->namespace = $namespace;

        return $this;
    }

    /**
     * Set base path for files.
     *
     * @param string $path
     *
     * @return self
     */
    public function rootPath(string $path): self
    {
        $this->rootPath = $path;

        return $this;
    }

    /**
     * Set root model filename without extensions.
     *
     * @param string $rootModel
     *
     * @return self
     */
    public function rootModel(string $rootModel): self
    {
        $this->rootModel = $rootModel;

        return $this;
    }

    /**
     * Create files with strict types declaration.
     *
     * @param bool $add
     *
     * @return self
     */
    public function strictTypes(bool $add): self
    {
        $this->strictTypes = $add;

        return $this;
    }

    /**
     * Ignore file, if its already exists.
     *
     * @param bool $override
     *
     * @return self
     */
    public function overrideExists(bool $override): self
    {
        $this->overrideExists = $override;

        return $this;
    }

    /**
     * Try to normalize objects that has numeric keys. Something this may means just broken array but not an object.
     *
     * @param bool $normalize
     *
     * @return self
     */
    public function normalizeArray(bool $normalize): self
    {
        $this->normalizeArray = $normalize;

        return $this;
    }

    /**
     * @param string $json
     *
     * @return bool
     */
    public function generate(string $json): bool
    {
        $this->generatedClasses = [];

        if (!str_ends_with($this->namespace, '\\')) {
            $this->namespace .= '\\';
        }

        if (empty($json)) {
            throw new Exception('JSON string could not be empty');
        }

        $root      = json_decode($json);
        $structure = $this->proceedNode($root);

        $filesStructure = [
            self::KEY_TYPE         => $this->propertyType(''),
            self::KEY_SUBSTRUCTURE => $structure,
        ];
        $this->buildFile($filesStructure);

        return true;
    }

    /**
     * Find unique (for current generation) class name.
     *
     * @param array $params
     *
     * @return string
     */
    protected function resolveClassName(array $params): string
    {
        $className = $params[self::KEY_TYPE];

        if (!isset($this->generatedClasses[$className])) {
            $this->generatedClasses[$className] = $params[self::KEY_SUBSTRUCTURE];

            return $className;
        }

        for ($i = 1; true; $i++) {
            $tmpClassName = $className . $i;

            if (isset($this->generatedClasses[$tmpClassName])) {
                continue;
            }

            $this->generatedClasses[$tmpClassName] = $params[self::KEY_SUBSTRUCTURE];

            return $tmpClassName;
        }
    }

    /**
     * @param stdClass $node
     *
     * @return array
     */
    private function proceedNode(stdClass $node): array
    {
        $properties = [];

        foreach (get_object_vars($node) as $name => $property) {
            $properties[$name] = $this->proceedNodeProperty($name, $property);
        }

        return $properties;
    }

    /**
     * @param string $name
     * @param mixed $property
     *
     * @return array
     */
    private function proceedNodeProperty(string $name, mixed $property): array
    {
        if (null === $property) {
            return [];
        }

        if (is_scalar($property)) {
            return [
                self::KEY_TYPE => get_debug_type($property),
            ];
        }

        // Handle case when it's an array, but with numeric keys (so parsed as object).
        $property = $this->normalizeArrayIfNeeded($property);

        if (is_object($property)) {
            return [
                self::KEY_TYPE => $this->propertyType($name),
                self::KEY_SUBSTRUCTURE => $this->proceedNode($property),
            ];
        }

        // From here $property is an array

        if (empty($property)) {
            return [
                self::KEY_ARRAY_LEVEL => 1,
            ];
        }

        $subStructure = null;
        foreach ($property as $subProperty) {
            $subStructure = $this->mergeProperties($subStructure ?? [], $this->proceedNodeProperty($name, $subProperty));
        }

        $subStructure[self::KEY_ARRAY_LEVEL] = ($subStructure[self::KEY_ARRAY_LEVEL] ?? 0) + 1;

        return $subStructure;
    }

    /**
     * @param object|array $property
     *
     * @return object|array
     */
    private function normalizeArrayIfNeeded(object|array $property): object|array
    {
        if (!$this->normalizeArray) {
            return $property;
        }

        if (!is_object($property)) {
            return $property;
        }

        $keys = array_keys(get_object_vars($property));
        if (empty($keys)) {
            return $property;
        }

        $justNumericKeys = true;
        foreach ($keys as $key) {
            $justNumericKeys = $justNumericKeys && is_numeric($key);
        }
        if ($justNumericKeys) {
            $property = (array) $property;
        }

        return $property;
    }

    /**
     * @param string $name
     *
     * @return string
     */
    private function propertyType(string $name): string
    {
        return $this->namespace . $this->rootModel . str_replace(['-', '_'], '', ucwords($name, '-_'));
    }

    /**
     * @param array $params
     *
     * @return bool
     */
    private function buildFile(array &$params): bool
    {
        $globalNamespace = __NAMESPACE__;

        $params[self::KEY_TYPE] = $this->resolveClassName($params);

        $namespace = substr($params[self::KEY_TYPE], 0, strrpos($params[self::KEY_TYPE], '\\'));
        $className = substr($params[self::KEY_TYPE], strrpos($params[self::KEY_TYPE], '\\') + 1);
        $use       = [];

        $properties = [];
        foreach ($params[self::KEY_SUBSTRUCTURE] as $subName => $subParams) {
            if (empty($subParams[self::KEY_TYPE])) {
                $subParams[self::KEY_TYPE] = 'string';
            }

            $maxArrayLevel    = $subParams[self::KEY_ARRAY_LEVEL] ?? 0;
            $isArrayOfObjects = false;
            if (str_contains($subParams[self::KEY_TYPE], '\\')) {
                $this->buildFile($subParams);
                $isArrayOfObjects          = true;
                $subParams[self::KEY_TYPE] = substr($subParams[self::KEY_TYPE], strrpos($subParams[self::KEY_TYPE], '\\') + 1);
            }

            $type = $subParams[self::KEY_TYPE];

            $attributes = [];

            // Clear property name
            if (0 === preg_match('/' . self::PROPERTY_NAME_REGEX . '/', $subName)) {
                $use[]        = "use {$globalNamespace}\Attributes\Map;";
                $attributes[] = '    #[Map(' . var_export($subName, true) . ')]';
                $subName      = preg_replace('/' . self::PROPERTY_INVALID_PREFIX . '/', '', $subName);
                $subName      = preg_replace('/' . self::PROPERTY_INVALID_SUFFIX . '/', '', $subName);
            }

            if ($maxArrayLevel) {
                $use[]        = "use {$globalNamespace}\Attributes\AsArray;";
                $itemType     = $isArrayOfObjects ? "{$type}::class" : ('\'' . $type . '\'');
                $level        = ($maxArrayLevel > 1) ? ", level: {$maxArrayLevel}" : '';
                $attributes[] = "    #[AsArray(itemType: {$itemType}{$level})]";
            }

            $properties[] = $this->generatePropertyFileContent($subName, $type, $maxArrayLevel, $attributes);
        }

        $use = array_flip($use);
        ksort($use);
        $use = array_keys($use);

        $file = $this->generateClassFileContent($className, $namespace, $properties, $use, $this->strictTypes);

        $fileName = ($this->rootPath ?: str_replace('\\', '/', $className)) . '/' . $className . '.php';

        if (!$this->overrideExists && is_file($fileName)) {
            return true;
        }

        return false !== file_put_contents($fileName, $file);
    }

    /**
     * @param array<int|string, mixed> $array1
     * @param array<int|string, mixed> $array2
     *
     * @return array<int|string, mixed>
     */
    private function mergeProperties(array $array1, array $array2): array
    {
        if (empty($array1)) {
            return $array2;
        }

        // Check both structures are array
        if (($array1[self::KEY_ARRAY_LEVEL] ?? 0) !== ($array2[self::KEY_ARRAY_LEVEL] ?? 0)) {
            return array_filter([
                self::KEY_TYPE => 'mixed',
                self::KEY_ARRAY_LEVEL => min($array1[self::KEY_ARRAY_LEVEL] ?? 0, ($array2[self::KEY_ARRAY_LEVEL] ?? 0)),
            ]);
        }

        if ($array1[self::KEY_TYPE] !== $array2[self::KEY_TYPE]) {
            return array_filter([
                self::KEY_TYPE => 'mixed',
                self::KEY_ARRAY_LEVEL => $array1[self::KEY_ARRAY_LEVEL] ?? 0,
            ]);
        }

        $merged = $array1;
        foreach ($array2[self::KEY_SUBSTRUCTURE] ?? [] as $key => $properties) {
            if (!isset($merged[self::KEY_SUBSTRUCTURE][$key])) {
                $merged[self::KEY_SUBSTRUCTURE][$key] = $properties;

                continue;
            }

            $merged[self::KEY_SUBSTRUCTURE][$key] = $this->mergeProperties($merged[self::KEY_SUBSTRUCTURE][$key], $properties);
        }

        return $merged;
    }

    /**
     * @param string $name
     * @param string $type
     * @param int $maxArrayLevel
     * @param array $attributes
     *
     * @return string
     */
    private function generatePropertyFileContent(string $name, string $type, int $maxArrayLevel, array $attributes): string
    {
        $stub = file_get_contents(self::STUBS_PATH . '/property.stub');

        $phpDocType = $type;
        if ($maxArrayLevel) {
            for ($arrayLevel = $maxArrayLevel; $arrayLevel > 0; $arrayLevel--) {
                $phpDocType = "array<{$phpDocType}>";
            }
            $type = 'array';
        }
        if (!in_array($type, ['mixed', 'array'])) {
            $phpDocType = $type = "?{$type}";
        }

        return str_replace([
            '{{phpdoc_type}}',
            '{{name}}',
            '{{attributes}}',
            '{{type}}',
            '{{default}}',
        ], [
            $phpDocType,
            $name,
            empty($attributes) ? '' : ("\n" . implode("\n", $attributes)),
            $type,
            $maxArrayLevel ? '[]' : 'null',
        ], $stub);
    }

    /**
     * @param string $className
     * @param string $namespace
     * @param array $properties
     * @param array $use
     * @param bool $strictTypes
     *
     * @return string
     */
    private function generateClassFileContent(string $className, string $namespace, array $properties, array $use, bool $strictTypes): string
    {
        $stub = file_get_contents(self::STUBS_PATH . '/class.stub');

        return str_replace([
            '{{strict_types}}',
            '{{namespace}}',
            '{{use}}',
            '{{class_name}}',
            '{{properties}}',
        ], [
            $strictTypes ? ("\n" . 'declare(strict_types=1);' . "\n") : '',
            $namespace,
            empty($use) ? '' : ("\n" . implode("\n", $use) . "\n"),
            $className,
            empty($properties) ? '' : ("\n" . implode("\n" . "\n", $properties)),
        ], $stub);
    }
}

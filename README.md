# JSON to Model

Simple and fast package to convert JSON into plain PHP objects.

## Why?

Working with third-party APIs often means dealing with raw arrays or stdClass objects.

This package allows you to:

- Convert JSON into typed PHP objects
- Use IDE autocompletion and static analysis
- Keep your code clean and maintainable
- Do it with performance in mind

## Installation

```sh
composer require zamaldev/json-model
```

## Basic Usage

```php
use Zamaldev\JsonModel\JsonModel;

require 'vendor/autoload.php';

$data = '{"key":"value","key2":2}';

class Model {
    public string $key;

    public int $key2;
}

$parser = new JsonModel();

$model = $parser->parse($data, Model::class);

echo $model->key; // "value"
echo $model->key2; // 2
```

## Key mapping

You can use `Attributes\Map` property attribute, so map JSON keys to model properties.

```php
use Zamaldev\JsonModel\Attributes\Map;
use Zamaldev\JsonModel\JsonModel;

require 'vendor/autoload.php';

$data = '{"key":"value","key2":2}';

class Model {
    #[Map(name: 'key')]
    public string $string;

    #[Map(name: 'key2')]
    public int $int;
}

$parser = new JsonModel();

$model = $parser->parse($data, Model::class);

echo $model->string; // "value"
echo $model->int; // 2
```

## Arrays

To parse JSON arrays into array of models or scalars, `Attributes\AsArray` property attribute can be used. By default, array will be parsed into array of `string`, but you can specify type with additional properties.

```php
use Zamaldev\JsonModel\Attributes\AsArray;
use Zamaldev\JsonModel\JsonModel;

require 'vendor/autoload.php';

$data = '{"key":["value"],"key2":[1,2],"key3":[{"obj_key":"obj_val"}]}';

class Model {
    #[AsArray()] // by default 'string'
    public array $key;

    #[AsArray(itemType: 'int')] // or 'integer'
    public array $key2;

    #[AsArray(itemType: SubModel::class)]
    public array $key3;
}

class SubModel {
    public string $obj_key;
}

$parser = new JsonModel();

$model = $parser->parse($data, Model::class);

echo $model->key[0]; // "value"
echo $model->key2[0]; // 1
echo $model->key2[1]; // 2
echo $model->key3[0]->obj_key; // "obj_val"
```

# JSON to Model

Simple and fast package to convert JSON into plain PHP objects.

## Why?

Working with third-party APIs often means dealing with raw arrays or stdClass objects.

This package allows you to:

- Convert JSON into typed PHP objects
- Use IDE autocompletion and static analysis
- Keep your code clean and maintainable
- Generate model directly from response
- Do it with performance in mind

## Requirements

- PHP 8+

## Installation

```sh
composer require zamaldev/json-model
```

## Basic Usage

```php
use Zamaldev\JsonModel\JsonModel;

require 'vendor/autoload.php';

class Model {
    public string $key;

    public int $key2;
}

$parser = new JsonModel();

$data = '{"key":"value","key2":2}';

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

class Model {
    #[Map(name: 'key')]
    public string $string;

    #[Map(name: 'key2')]
    public int $int;
}

$parser = new JsonModel();

$data = '{"key":"value","key2":2}';

$model = $parser->parse($data, Model::class);

echo $model->string; // "value"
echo $model->int; // 2
```

## Arrays

To parse JSON arrays into array of models or scalars, `Attributes\AsArray` property attribute can be used. By default, array will be parsed into array of `string`, but you can specify type with additional properties. For nested array you could set nesting level by providing `level` property to `Attributes\AsArray` attribute.

```php
use Zamaldev\JsonModel\Attributes\AsArray;
use Zamaldev\JsonModel\JsonModel;

require 'vendor/autoload.php';

class Model {
    #[AsArray()] // by default 'string'
    public array $key;

    #[AsArray(itemType: 'int')] // or 'integer'
    public array $key2;

    #[AsArray(itemType: SubModel::class)]
    public array $key3;

    #[AsArray(itemType: 'int', level: 4)]
    public array $key4;
}

class SubModel {
    public string $obj_key;
}

$parser = new JsonModel();

$data = '{"key":["value"],"key2":[1,2],"key3":[{"obj_key":"obj_val"}],"key4":[[[[3]]]]}';

$model = $parser->parse($data, Model::class);

echo $model->key[0]; // "value"
echo $model->key2[0]; // 1
echo $model->key2[1]; // 2
echo $model->key3[0]->obj_key; // "obj_val"
echo $model->key4[0][0][0][0]; // 3
```

## DNF types

While working with inconsistent APIs, sometime you may need to support few types per one property. This is possible using `Attributes\Caster` interface. However, only you know the resolution logic, so you should write your own implementation for your specific case. Casters works with scalar typed properties and with arrays. If you need to cast value to any model, you have the `$jsonModel` parameter, this is current `JSONModel` instance, so you could use `parse` method.

```php
use Zamaldev\JsonModel\Attributes\Caster;
use Zamaldev\JsonModel\JsonModel;
use Zamaldev\JsonModel\JsonModelInterface;

require 'vendor/autoload.php';

#[Attribute(Attribute::TARGET_PROPERTY)]
class CustomCaster implements Caster
{
    public function cast(JsonModelInterface $jsonModel, mixed $value): mixed
    {
        // Here will be your logic.
        if (is_array($value)) {
            return $value;
        }
        if (is_object($value)) {
            return $jsonModel->parse($value, Model::class);
        }

        return (string) $value;
    }
}

class Model {
    #[CustomCaster]
    public self|string|array $result;
}

$parser = new JsonModel();

$data = '{"result":["value"]}';

$model = $parser->parse($data, Model::class);
echo $model->result[0]; // "value"

$data = '{"result":"error"}';

$model = $parser->parse($data, Model::class);
echo $model->result; // "error"

$data = '{"result":{"result":"ok"}}';

$model = $parser->parse($data, Model::class);
echo $model->result->result; // "ok"
```

## Sanitizer

Don't blind trust any third party API you work with. It is important to validate or at least simple sanitize the input. For this purpose, you can use `Attributes\Sanitizer` interface. Package already provides `Attributes\SimpleSanitizer`, but you can write your own for your needs.

```php
use Zamaldev\JsonModel\Attributes\SimpleSanitizer;
use Zamaldev\JsonModel\JsonModel;

require 'vendor/autoload.php';

class Model
{
    #[SimpleSanitizer('trim')]
    #[SimpleSanitizer('strtolower')]
    #[SimpleSanitizer('ucfirst')]
    public string $result;
}

$parser = new JsonModel();

$data = '{"result":"   vAlUe   "}';

$model = $parser->parse($data, Model::class);
echo $model->result; // "Value"
```

Note attributes order. As you can see from example, it goes from top to bottom, so sanitize chain will goes like this:

`"   vAlUe   " -(trim)-> "vAlUe" -(strtolower)-> "value" -(ucfirst)-> "Value"`

## Generator

Working with many third party requests and huge responses, it is useful to just autogenerate everything. For this purposes `JsonGenerator` class exists.

Here is an example on how to use it:

```php
use Zamaldev\JsonModel\JsonGenerator;

require 'vendor/autoload.php';

$data = '{"key":"value","-_key2":1,"key3":{"obj_key":"obj_val"},"key4":[[[[{"num": true, "key3":{"obj_key": "obj_val"}}]]]],"key5":[1,"a"]}';

(new JsonGenerator())
    ->namespace('Demo')
    ->rootModel('Demo')
    ->rootPath(__DIR__)
    ->strictTypes(true)
    ->generate($data);
```

This script will generate those files:

```php
// Demo.php
<?php

declare(strict_types=1);

namespace Demo;

use Zamaldev\JsonModel\Attributes\AsArray;
use Zamaldev\JsonModel\Attributes\Map;

class Demo
{
    /**
     * @var ?string $key
     */
    public ?string $key = null;

    /**
     * @var ?int $_key2
     */
    #[Map('-_key2')]
    public ?int $_key2 = null;

    /**
     * @var ?DemoKey3 $key3
     */
    public ?DemoKey3 $key3 = null;

    /**
     * @var array<array<array<array<DemoKey4>>>> $key4
     */
    #[AsArray(itemType: DemoKey4::class, level: 4)]
    public array $key4 = [];

    /**
     * @var array<mixed> $key5
     */
    #[AsArray(itemType: 'mixed')]
    public array $key5 = [];
}
```

```php
// DemoKey3.php
<?php

declare(strict_types=1);

namespace Demo;

class DemoKey3
{
    /**
     * @var ?string $obj_key
     */
    public ?string $obj_key = null;
}
```

```php
// DemoKey4.php
<?php

declare(strict_types=1);

namespace Demo;

class DemoKey4
{
    /**
     * @var ?bool $num
     */
    public ?bool $num = null;

    /**
     * @var ?DemoKey31 $key3
     */
    public ?DemoKey31 $key3 = null;
}
```

```php
// DemoKey31.php
<?php

declare(strict_types=1);

namespace Demo;

class DemoKey31
{
    /**
     * @var ?string $obj_key
     */
    public ?string $obj_key = null;
}
```

Some rules on how generator works:
- New class names generated at `[root model][key]`
- If key has prohibited symbols for class properties, they are simply omitted, but `Attributes\Map` is added
- When there same class name is used for different objects (even with same structure), they resolved with incremental index `[root model][key][index]`
- If array has different types inside, `mixed` type will be used, so the json values will be used "as is"

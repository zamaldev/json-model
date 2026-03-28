<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Zamaldev\JsonModel\Attributes\SimpleSanitizer;
use Zamaldev\JsonModel\JsonModel;

class SanitizerTest extends TestCase
{
    public function testParse_no_sanitizer()
    {
        $parser = new JsonModel();

        $data = [
            'value' => '   value   ',
        ];

        $model = $parser->parse($data, NoSanitizedModel::class);

        $this->assertSame('   value   ', $model->value);
    }

    public function testParse_one_sanitizer()
    {
        $parser = new JsonModel();

        $data = [
            'value' => '   value   ',
        ];

        $model = $parser->parse($data, OneSanitizedModel::class);

        $this->assertSame('value', $model->value);
    }

    public function testParse_few_sanitizer()
    {
        $parser = new JsonModel();

        $data = [
            'value' => '   value   ',
        ];

        $model = $parser->parse($data, FewSanitizedModel::class);

        $this->assertSame('Value', $model->value);
    }

    public function testParse_ordered_sanitizer()
    {
        $parser = new JsonModel();

        $data = [
            'value' => '   value   ',
        ];

        $model = $parser->parse($data, OrderedSanitizedModel::class);

        $this->assertSame('value', $model->value);
    }

    public function testParse_array_ordered_sanitizer()
    {
        $parser = new JsonModel();

        $data = [
            'values' => [
                '   value1   ',
                '   value2   ',
                '   value3   ',
            ],
        ];

        $model = $parser->parse($data, ArrayOrderedSanitizedModel::class);

        $this->assertSame(['value1', 'value2', 'value3'], $model->values);
    }
}

class NoSanitizedModel
{
    public string $value;
}

class OneSanitizedModel
{
    #[SimpleSanitizer('trim')]
    public string $value;
}

class FewSanitizedModel
{
    #[SimpleSanitizer('trim')]
    #[SimpleSanitizer('ucfirst')]
    public string $value;
}

class OrderedSanitizedModel
{
    #[SimpleSanitizer('trim')]
    #[SimpleSanitizer('ucfirst')]
    #[SimpleSanitizer('strtolower')]
    public string $value;
}

class ArrayOrderedSanitizedModel
{
    #[SimpleSanitizer('trim')]
    #[SimpleSanitizer('ucfirst')]
    #[SimpleSanitizer('strtolower')]
    public array $values;
}

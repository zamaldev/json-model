<?php

declare(strict_types=1);

namespace Tests\Unit;

use Exception;
use PHPUnit\Framework\TestCase;
use Tests\Model\Model1;
use Tests\Model\ModelNestedArray;
use Zamaldev\JsonModel\Attributes\AsArray;
use Zamaldev\JsonModel\JsonModel;

class BasicTest extends TestCase
{
    public function testParse_from_array()
    {
        $parser = new JsonModel();

        $data = [
            'string' => 'test_string',
            'int' => 12345,
            'bool' => true,
            'float' => 123.45678,
            'null_str' => 'string',
            'array' => [1, '2', 3.0, true],
            'arrayOfNumbers' => [1, '2', 3.0, true],
            'arrayOfObjects' => [
                [
                    'string1' => 'string1_data1',
                    'string2' => 'string2_data1',
                    'string3' => 'string3_data1',
                    'string4' => 'string4_data1',
                    'string5' => 'string5_data1',
                    'string6' => 'string6_data1',
                    'string7' => 'string7_data1',
                    'string8' => 'string8_data1',
                    'string9' => 'string9_data1',
                    'string10' => 'string10_data1',
                    'string11' => 'string11_data1',
                    'string12' => 'string12_data1',
                    'string13' => 'string13_data1',
                    'string14' => 'string14_data1',
                    'string15' => 'string15_data1',
                    'string16' => 'string16_data1',
                    'string17' => 'string17_data1',
                    'string18' => 'string18_data1',
                    'string19' => 'string19_data1',
                    'string20' => 'string20_data1',
                ],
                [
                    'string1' => 'string1_data2',
                    'string2' => 'string2_data2',
                    'string3' => 'string3_data2',
                    'string4' => 'string4_data2',
                    'string5' => 'string5_data2',
                    'string6' => 'string6_data2',
                    'string7' => 'string7_data2',
                    'string8' => 'string8_data2',
                    'string9' => 'string9_data2',
                    'string10' => 'string10_data2',
                    'string11' => 'string11_data2',
                    'string12' => 'string12_data2',
                    'string13' => 'string13_data2',
                    'string14' => 'string14_data2',
                    'string15' => 'string15_data2',
                    'string16' => 'string16_data2',
                    'string17' => 'string17_data2',
                    'string18' => 'string18_data2',
                    'string19' => 'string19_data2',
                    'string20' => 'string20_data2',
                ],
            ],
        ];

        $model = $parser->parse($data, Model1::class);

        $this->assertSame('test_string', $model->string);
        $this->assertSame(12345, $model->int);
        $this->assertSame(true, $model->bool);
        $this->assertSame(123.45678, $model->float);
        $this->assertSame('string', $model->nullableString);
        $this->assertSame(['1', '2', '3', '1'], $model->array);
        $this->assertSame([1, 2, 3, 1], $model->arrayOfNumbers);
        $this->assertCount(2, $model->arrayOfObjects);
        $this->assertSame('string1_data1', $model->arrayOfObjects[0]->string1);
        $this->assertSame('string12_data1', $model->arrayOfObjects[0]->string12);
        $this->assertSame('string1_data2', $model->arrayOfObjects[1]->string1);
        $this->assertSame('string12_data2', $model->arrayOfObjects[1]->string12);
    }

    public function testParse_from_string()
    {
        $parser = new JsonModel();

        $data = json_encode([
            'string' => 'test_string',
            'int' => 12345,
            'bool' => true,
            'float' => 123.45678,
            'null_str' => 'string',
            'array' => [1, '2', 3.0, true],
            'arrayOfNumbers' => [1, '2', 3.0, true],
            'arrayOfObjects' => [
                [
                    'string1' => 'string1_data1',
                    'string2' => 'string2_data1',
                    'string3' => 'string3_data1',
                    'string4' => 'string4_data1',
                    'string5' => 'string5_data1',
                    'string6' => 'string6_data1',
                    'string7' => 'string7_data1',
                    'string8' => 'string8_data1',
                    'string9' => 'string9_data1',
                    'string10' => 'string10_data1',
                    'string11' => 'string11_data1',
                    'string12' => 'string12_data1',
                    'string13' => 'string13_data1',
                    'string14' => 'string14_data1',
                    'string15' => 'string15_data1',
                    'string16' => 'string16_data1',
                    'string17' => 'string17_data1',
                    'string18' => 'string18_data1',
                    'string19' => 'string19_data1',
                    'string20' => 'string20_data1',
                ],
                [
                    'string1' => 'string1_data2',
                    'string2' => 'string2_data2',
                    'string3' => 'string3_data2',
                    'string4' => 'string4_data2',
                    'string5' => 'string5_data2',
                    'string6' => 'string6_data2',
                    'string7' => 'string7_data2',
                    'string8' => 'string8_data2',
                    'string9' => 'string9_data2',
                    'string10' => 'string10_data2',
                    'string11' => 'string11_data2',
                    'string12' => 'string12_data2',
                    'string13' => 'string13_data2',
                    'string14' => 'string14_data2',
                    'string15' => 'string15_data2',
                    'string16' => 'string16_data2',
                    'string17' => 'string17_data2',
                    'string18' => 'string18_data2',
                    'string19' => 'string19_data2',
                    'string20' => 'string20_data2',
                ],
            ],
        ]);

        $model = $parser->parse($data, Model1::class);

        $this->assertSame('test_string', $model->string);
        $this->assertSame(12345, $model->int);
        $this->assertSame(true, $model->bool);
        $this->assertSame(123.45678, $model->float);
        $this->assertSame('string', $model->nullableString);
        $this->assertSame(['1', '2', '3', '1'], $model->array);
        $this->assertSame([1, 2, 3, 1], $model->arrayOfNumbers);
        $this->assertCount(2, $model->arrayOfObjects);
        $this->assertSame('string1_data1', $model->arrayOfObjects[0]->string1);
        $this->assertSame('string12_data1', $model->arrayOfObjects[0]->string12);
        $this->assertSame('string1_data2', $model->arrayOfObjects[1]->string1);
        $this->assertSame('string12_data2', $model->arrayOfObjects[1]->string12);
    }

    public function testParse_from_stdClass()
    {
        $parser = new JsonModel();

        $data = json_decode(json_encode([
            'string' => 'test_string',
            'int' => 12345,
            'bool' => true,
            'float' => 123.45678,
            'null_str' => 'string',
            'array' => [1, '2', 3.0, true],
            'arrayOfNumbers' => [1, '2', 3.0, true],
            'arrayOfObjects' => [
                [
                    'string1' => 'string1_data1',
                    'string2' => 'string2_data1',
                    'string3' => 'string3_data1',
                    'string4' => 'string4_data1',
                    'string5' => 'string5_data1',
                    'string6' => 'string6_data1',
                    'string7' => 'string7_data1',
                    'string8' => 'string8_data1',
                    'string9' => 'string9_data1',
                    'string10' => 'string10_data1',
                    'string11' => 'string11_data1',
                    'string12' => 'string12_data1',
                    'string13' => 'string13_data1',
                    'string14' => 'string14_data1',
                    'string15' => 'string15_data1',
                    'string16' => 'string16_data1',
                    'string17' => 'string17_data1',
                    'string18' => 'string18_data1',
                    'string19' => 'string19_data1',
                    'string20' => 'string20_data1',
                ],
                [
                    'string1' => 'string1_data2',
                    'string2' => 'string2_data2',
                    'string3' => 'string3_data2',
                    'string4' => 'string4_data2',
                    'string5' => 'string5_data2',
                    'string6' => 'string6_data2',
                    'string7' => 'string7_data2',
                    'string8' => 'string8_data2',
                    'string9' => 'string9_data2',
                    'string10' => 'string10_data2',
                    'string11' => 'string11_data2',
                    'string12' => 'string12_data2',
                    'string13' => 'string13_data2',
                    'string14' => 'string14_data2',
                    'string15' => 'string15_data2',
                    'string16' => 'string16_data2',
                    'string17' => 'string17_data2',
                    'string18' => 'string18_data2',
                    'string19' => 'string19_data2',
                    'string20' => 'string20_data2',
                ],
            ],
        ]));

        $model = $parser->parse($data, Model1::class);

        $this->assertSame('test_string', $model->string);
        $this->assertSame(12345, $model->int);
        $this->assertSame(true, $model->bool);
        $this->assertSame(123.45678, $model->float);
        $this->assertSame('string', $model->nullableString);
        $this->assertSame(['1', '2', '3', '1'], $model->array);
        $this->assertSame([1, 2, 3, 1], $model->arrayOfNumbers);
        $this->assertCount(2, $model->arrayOfObjects);
        $this->assertSame('string1_data1', $model->arrayOfObjects[0]->string1);
        $this->assertSame('string12_data1', $model->arrayOfObjects[0]->string12);
        $this->assertSame('string1_data2', $model->arrayOfObjects[1]->string1);
        $this->assertSame('string12_data2', $model->arrayOfObjects[1]->string12);
    }

    public function testParse_string_from_string()
    {
        $parser = new JsonModel();

        $data = [
            'string' => 'test_string',
        ];

        $model = $parser->parse($data, Model1::class);

        $this->assertSame('test_string', $model->string);
    }

    public function testParse_string_from_int()
    {
        $parser = new JsonModel();

        $data = [
            'string' => 5,
        ];

        $model = $parser->parse($data, Model1::class);

        $this->assertSame('5', $model->string);
    }

    public function testParse_string_from_float()
    {
        $parser = new JsonModel();

        $data = [
            'string' => 3.1,
        ];

        $model = $parser->parse($data, Model1::class);

        $this->assertSame('3.1', $model->string);
    }

    public function testParse_string_from_bool()
    {
        $parser = new JsonModel();

        $data = [
            'string' => false,
        ];

        $model = $parser->parse($data, Model1::class);

        $this->assertSame('', $model->string);
    }

    public function testParse_unknown_type()
    {
        $parser = new JsonModel();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Cannot found type of value. It must have named type or has attribute that implements Caster interface.');
        $parser->parse(['value' => 'string'], Model_unknown_type::class);
    }

    public function testParse_unknown_array_type()
    {
        $parser = new JsonModel();

        $model = $parser->parse(['array' => ['string']], Model_unknown_array_type::class);

        $this->assertSame('string', $model->array[0]);
    }

    public function testParse_missing_array_type()
    {
        $parser = new JsonModel();

        $model = $parser->parse(['unknownArray' => ['string']], Model_missing_array_type::class);

        $this->assertSame('string', $model->unknownArray[0]);
    }

    public function testParse_nested_array()
    {
        $parser = new JsonModel();

        $data = [1,2,3];
        for ($i = 0; $i < 99; $i++) {
            $data = [$data];
        }

        $model = $parser->parse(['data' => $data], ModelNestedArray::class);

        $modelData = $model->data;
        for ($i = 0; $i < 99; $i++) {
            $modelData = reset($modelData);
        }
        $this->assertSame([1, 2, 3], $modelData);
    }
}

class Model_unknown_type
{
    public $value;
}

class Model_unknown_array_type
{
    public array $array;
}

class Model_missing_array_type
{
    #[AsArray(itemType: null)]
    public array $unknownArray;
}

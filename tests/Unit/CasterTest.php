<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Tests\Model\Model3;
use Zamaldev\JsonModel\JsonModel;

class CasterTest extends TestCase
{
    public function testParse_cast()
    {
        $parser = new JsonModel();

        $data = [
            'untyped' => 'value',
            'mixed' => 'value',
            'union' => 'value',
            'intersect' => 'value',
            'dnf1' => 'value',
            'dnf2' => 'value',
            'array' => [
                'string',
                'int',
                'bool',
                'float',
                'object',
            ],
        ];

        $model = $parser->parse($data, Model3::class);

        $this->assertSame('cast', $model->untyped);
        $this->assertSame('cast', $model->mixed);
        $this->assertSame('cast', $model->union);
        $this->assertSame('cast', $model->intersect->text);
        $this->assertSame('cast', $model->dnf1);
        $this->assertSame('cast', $model->dnf2->text);
        $this->assertSame('string', $model->array[0]);
        $this->assertSame(5, $model->array[1]);
        $this->assertSame(true, $model->array[2]);
        $this->assertSame(3.14, $model->array[3]);
        $this->assertSame('cast', $model->array[4]->text);
    }
}

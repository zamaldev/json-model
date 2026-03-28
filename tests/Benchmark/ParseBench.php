<?php

declare(strict_types=1);

namespace Tests\Benchmark;

use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\Revs;
use PhpBench\Attributes\ParamProviders;
use Tests\Model\Model1;
use Tests\Model\Model2;
use Zamaldev\JsonModel\JsonModel;
use Zamaldev\JsonModel\JsonModelInterface;

#[Revs(10)]
#[Iterations(30)]
class ParseBench
{
    protected const FLAT_OBJECT = [
        'string1' => 'string1data',
        'string2' => 'string2data',
        'string3' => 'string3data',
        'string4' => 'string4data',
        'string5' => 'string5data',
        'string6' => 'string6data',
        'string7' => 'string7data',
        'string8' => 'string8data',
        'string9' => 'string9data',
        'string10' => 'string10data',
        'string11' => 'string11data',
        'string12' => 'string12data',
        'string13' => 'string13data',
        'string14' => 'string14data',
        'string15' => 'string15data',
        'string16' => 'string16data',
        'string17' => 'string17data',
        'string18' => 'string18data',
        'string19' => 'string19data',
        'string20' => 'string20data'
    ];

    public function __construct(
        protected JsonModelInterface $JsonModel = new JsonModel(),
    ) {}

    #[ParamProviders('provideFlat')]
    public function benchFlat($params): void
    {
        $this->JsonModel->parse($params['data'], Model2::class);
    }

    public function provideFlat()
    {
        yield ['data' => json_encode(self::FLAT_OBJECT)];
    }

    #[ParamProviders('provideSmallMixed')]
    public function benchSmallMixed($params): void
    {
        $this->JsonModel->parse($params['data'], Model1::class);
    }

    public function provideSmallMixed()
    {
        yield ['data' => json_encode([
            'string' => 'stringdata',
            'int' => 123456,
            'bool' => true,
            'float' => 213.1345678,
            'null_str' => null,
            'array' => [1, "2", 3.5, false, true, null],
            'arrayOfNumbers' => [1, 3, 5, 7, 9, 2, 4, 6, 8, 0],
            'arrayOfObjects' => array_fill(0, 1, self::FLAT_OBJECT),
        ])];
    }

    #[ParamProviders('provide100Mixed')]
    public function bench100Mixed($params): void
    {
        $this->JsonModel->parse($params['data'], Model1::class);
    }

    public function provide100Mixed()
    {
        yield ['data' => json_encode([
            'string' => 'stringdata',
            'int' => 123456,
            'bool' => true,
            'float' => 213.1345678,
            'null_str' => null,
            'array' => [1, "2", 3.5, false, true, null],
            'arrayOfNumbers' => [1, 3, 5, 7, 9, 2, 4, 6, 8, 0],
            'arrayOfObjects' => array_fill(0, 100, self::FLAT_OBJECT),
        ])];
    }

    #[ParamProviders('provide1000Mixed')]
    public function bench1000Mixed($params): void
    {
        $this->JsonModel->parse($params['data'], Model1::class);
    }

    public function provide1000Mixed()
    {
        yield ['data' => json_encode([
            'string' => 'stringdata',
            'int' => 123456,
            'bool' => true,
            'float' => 213.1345678,
            'null_str' => null,
            'array' => [1, "2", 3.5, false, true, null],
            'arrayOfNumbers' => [1, 3, 5, 7, 9, 2, 4, 6, 8, 0],
            'arrayOfObjects' => array_fill(0, 1000, self::FLAT_OBJECT),
        ])];
    }
}

<?php

declare(strict_types=1);

namespace Tests\Unit;

use Exception;
use PHPUnit\Framework\TestCase;
use Zamaldev\JsonModel\JsonGenerator;

class JsonGeneratorTest extends TestCase
{
    public string $tmpPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tmpPath = sys_get_temp_dir() . '/' . uniqid('phpunit_');
        if (!mkdir($this->tmpPath)) {
            throw new Exception("Failed to create tempdir");
        }
    }

    protected function tearDown(): void
    {
        $objects = scandir($this->tmpPath);
        foreach ($objects as $object) {
            if ($object === "." || $object === "..") {
                continue;
            }
            // echo file_get_contents($this->tmpPath . DIRECTORY_SEPARATOR . $object) . PHP_EOL;
            unlink($this->tmpPath . DIRECTORY_SEPARATOR . $object);
        }
        rmdir($this->tmpPath);

        parent::tearDown();
    }

    public function testGenerate_string()
    {
        $json = json_encode([
            'string' => 'string',
        ]);

        (new JsonGenerator())
            ->namespace('Tests\\Generated')
            ->rootPath($this->tmpPath)
            ->rootModel('GeneratedModelString')
            ->generate($json);

        $this->assertSame(<<<PHP
<?php

declare(strict_types=1);

namespace Tests\\Generated;

class GeneratedModelString
{
    /**
     * @var ?string \$string
     */
    public ?string \$string = null;
}

PHP, file_get_contents($this->tmpPath . '/GeneratedModelString.php'));
    }

    public function testGenerate_int()
    {
        $json = json_encode([
            'int' => 5,
        ]);

        (new JsonGenerator())
            ->namespace('Tests\\Generated')
            ->rootPath($this->tmpPath)
            ->rootModel('GeneratedModelInt')
            ->generate($json);

        $this->assertSame(<<<PHP
<?php

declare(strict_types=1);

namespace Tests\\Generated;

class GeneratedModelInt
{
    /**
     * @var ?int \$int
     */
    public ?int \$int = null;
}

PHP, file_get_contents($this->tmpPath . '/GeneratedModelInt.php'));
    }

    public function testGenerate_bool()
    {
        $json = json_encode([
            'bool' => true,
        ]);

        (new JsonGenerator())
            ->namespace('Tests\\Generated')
            ->rootPath($this->tmpPath)
            ->rootModel('GeneratedModelBool')
            ->generate($json);

        $this->assertSame(<<<PHP
<?php

declare(strict_types=1);

namespace Tests\\Generated;

class GeneratedModelBool
{
    /**
     * @var ?bool \$bool
     */
    public ?bool \$bool = null;
}

PHP, file_get_contents($this->tmpPath . '/GeneratedModelBool.php'));
    }

    public function testGenerate_float()
    {
        $json = json_encode([
            'float' => 3.14,
        ]);

        (new JsonGenerator())
            ->namespace('Tests\\Generated')
            ->rootPath($this->tmpPath)
            ->rootModel('GeneratedModelFloat')
            ->generate($json);

        $this->assertSame(<<<PHP
<?php

declare(strict_types=1);

namespace Tests\\Generated;

class GeneratedModelFloat
{
    /**
     * @var ?float \$float
     */
    public ?float \$float = null;
}

PHP, file_get_contents($this->tmpPath . '/GeneratedModelFloat.php'));
    }

    public function testGenerate_symbols()
    {
        $json = json_encode([
            '_-symbols' => 'string',
        ]);

        (new JsonGenerator())
            ->namespace('Tests\\Generated')
            ->rootPath($this->tmpPath)
            ->rootModel('GeneratedModelSymbols')
            ->generate($json);

        $this->assertSame(<<<PHP
<?php

declare(strict_types=1);

namespace Tests\\Generated;

use Zamaldev\\JsonModel\\Attributes\\Map;

class GeneratedModelSymbols
{
    /**
     * @var ?string \$_symbols
     */
    #[Map('_-symbols')]
    public ?string \$_symbols = null;
}

PHP, file_get_contents($this->tmpPath . '/GeneratedModelSymbols.php'));
    }

    public function testGenerate_invalid_property_key()
    {
        $json = json_encode([
            '-0_sym-bols' => 'string',
        ]);

        (new JsonGenerator())
            ->namespace('Tests\\Generated')
            ->rootPath($this->tmpPath)
            ->rootModel('GeneratedModelInvalidPropertyKey')
            ->generate($json);

        $this->assertSame(<<<PHP
<?php

declare(strict_types=1);

namespace Tests\\Generated;

use Zamaldev\\JsonModel\\Attributes\\Map;

class GeneratedModelInvalidPropertyKey
{
    /**
     * @var ?string \$_symbols
     */
    #[Map('-0_sym-bols')]
    public ?string \$_symbols = null;
}

PHP, file_get_contents($this->tmpPath . '/GeneratedModelInvalidPropertyKey.php'));
    }

    public function testGenerate_objects()
    {
        $json = json_encode([
            'object' => [
                'nested' => true,
            ],
        ]);

        (new JsonGenerator())
            ->namespace('Tests\\Generated')
            ->rootPath($this->tmpPath)
            ->rootModel('GeneratedModelObjects')
            ->generate($json);

        $this->assertSame(<<<PHP
<?php

declare(strict_types=1);

namespace Tests\\Generated;

class GeneratedModelObjects
{
    /**
     * @var ?GeneratedModelObjectsObject \$object
     */
    public ?GeneratedModelObjectsObject \$object = null;
}

PHP, file_get_contents($this->tmpPath . '/GeneratedModelObjects.php'));

        $this->assertSame(<<<PHP
<?php

declare(strict_types=1);

namespace Tests\\Generated;

class GeneratedModelObjectsObject
{
    /**
     * @var ?bool \$nested
     */
    public ?bool \$nested = null;
}

PHP, file_get_contents($this->tmpPath . '/GeneratedModelObjectsObject.php'));
    }

    public function testGenerate_objects_with_same_name()
    {
        $json = json_encode([
            'object' => [
                'nested' => true,
                'object' => [
                    'nested' => 'a',
                ],
            ],
        ]);

        (new JsonGenerator())
            ->namespace('Tests\\Generated')
            ->rootPath($this->tmpPath)
            ->rootModel('GeneratedModelObjects')
            ->generate($json);

        $this->assertSame(<<<PHP
<?php

declare(strict_types=1);

namespace Tests\\Generated;

class GeneratedModelObjects
{
    /**
     * @var ?GeneratedModelObjectsObject \$object
     */
    public ?GeneratedModelObjectsObject \$object = null;
}

PHP, file_get_contents($this->tmpPath . '/GeneratedModelObjects.php'));

        $this->assertSame(<<<PHP
<?php

declare(strict_types=1);

namespace Tests\\Generated;

class GeneratedModelObjectsObject
{
    /**
     * @var ?bool \$nested
     */
    public ?bool \$nested = null;

    /**
     * @var ?GeneratedModelObjectsObject1 \$object
     */
    public ?GeneratedModelObjectsObject1 \$object = null;
}

PHP, file_get_contents($this->tmpPath . '/GeneratedModelObjectsObject.php'));

        $this->assertSame(<<<PHP
<?php

declare(strict_types=1);

namespace Tests\\Generated;

class GeneratedModelObjectsObject1
{
    /**
     * @var ?string \$nested
     */
    public ?string \$nested = null;
}

PHP, file_get_contents($this->tmpPath . '/GeneratedModelObjectsObject1.php'));
    }

    public function testGenerate_array_of_int()
    {
        $json = json_encode([
            'arrayOfInt' => [1, 2, 3, 4],
        ]);

        (new JsonGenerator())
            ->namespace('Tests\\Generated')
            ->rootPath($this->tmpPath)
            ->rootModel('GeneratedModelArrayOfInt')
            ->generate($json);

        $this->assertSame(<<<PHP
<?php

declare(strict_types=1);

namespace Tests\\Generated;

use Zamaldev\\JsonModel\\Attributes\\AsArray;

class GeneratedModelArrayOfInt
{
    /**
     * @var array<int> \$arrayOfInt
     */
    #[AsArray(itemType: 'int')]
    public array \$arrayOfInt = [];
}

PHP, file_get_contents($this->tmpPath . '/GeneratedModelArrayOfInt.php'));
    }

    public function testGenerate_array_of_string()
    {
        $json = json_encode([
            'arrayOfString' => ['a', 'b', 'c', 'd'],
        ]);

        (new JsonGenerator())
            ->namespace('Tests\\Generated')
            ->rootPath($this->tmpPath)
            ->rootModel('GeneratedModelArrayOfString')
            ->generate($json);

        $this->assertSame(<<<PHP
<?php

declare(strict_types=1);

namespace Tests\\Generated;

use Zamaldev\\JsonModel\\Attributes\\AsArray;

class GeneratedModelArrayOfString
{
    /**
     * @var array<string> \$arrayOfString
     */
    #[AsArray(itemType: 'string')]
    public array \$arrayOfString = [];
}

PHP, file_get_contents($this->tmpPath . '/GeneratedModelArrayOfString.php'));
    }

    public function testGenerate_array_of_objects()
    {
        $json = json_encode([
            'arrayOfObjects' => [['a' => 1], ['b' => 'b']],
        ]);

        (new JsonGenerator())
            ->namespace('Tests\\Generated')
            ->rootPath($this->tmpPath)
            ->rootModel('GeneratedModelArrayOfObjects')
            ->generate($json);

        $this->assertSame(<<<PHP
<?php

declare(strict_types=1);

namespace Tests\\Generated;

use Zamaldev\\JsonModel\\Attributes\\AsArray;

class GeneratedModelArrayOfObjects
{
    /**
     * @var array<GeneratedModelArrayOfObjectsArrayOfObjects> \$arrayOfObjects
     */
    #[AsArray(itemType: GeneratedModelArrayOfObjectsArrayOfObjects::class)]
    public array \$arrayOfObjects = [];
}

PHP, file_get_contents($this->tmpPath . '/GeneratedModelArrayOfObjects.php'));

        $this->assertSame(<<<PHP
<?php

declare(strict_types=1);

namespace Tests\\Generated;

class GeneratedModelArrayOfObjectsArrayOfObjects
{
    /**
     * @var ?int \$a
     */
    public ?int \$a = null;

    /**
     * @var ?string \$b
     */
    public ?string \$b = null;
}

PHP, file_get_contents($this->tmpPath . '/GeneratedModelArrayOfObjectsArrayOfObjects.php'));
    }

    public function testGenerate_array_of_mixed_objects()
    {
        $json = json_encode([
            'arrayOfMixedObjects' => [['a' => 1], ['a' => 'a']],
        ]);

        (new JsonGenerator())
            ->namespace('Tests\\Generated')
            ->rootPath($this->tmpPath)
            ->rootModel('GeneratedModelArrayOfMixedObjects')
            ->generate($json);

        $this->assertSame(<<<PHP
<?php

declare(strict_types=1);

namespace Tests\\Generated;

use Zamaldev\\JsonModel\\Attributes\\AsArray;

class GeneratedModelArrayOfMixedObjects
{
    /**
     * @var array<GeneratedModelArrayOfMixedObjectsArrayOfMixedObjects> \$arrayOfMixedObjects
     */
    #[AsArray(itemType: GeneratedModelArrayOfMixedObjectsArrayOfMixedObjects::class)]
    public array \$arrayOfMixedObjects = [];
}

PHP, file_get_contents($this->tmpPath . '/GeneratedModelArrayOfMixedObjects.php'));

        $this->assertSame(<<<PHP
<?php

declare(strict_types=1);

namespace Tests\\Generated;

class GeneratedModelArrayOfMixedObjectsArrayOfMixedObjects
{
    /**
     * @var mixed \$a
     */
    public mixed \$a = null;
}

PHP, file_get_contents($this->tmpPath . '/GeneratedModelArrayOfMixedObjectsArrayOfMixedObjects.php'));
    }

    public function testGenerate_array_of_mixed()
    {
        $json = json_encode([
            'arrayOfMixed' => [1, 'a', true, 3.14],
        ]);

        (new JsonGenerator())
            ->namespace('Tests\\Generated')
            ->rootPath($this->tmpPath)
            ->rootModel('GeneratedModelArrayOfMixed')
            ->generate($json);

        $this->assertSame(<<<PHP
<?php

declare(strict_types=1);

namespace Tests\\Generated;

use Zamaldev\\JsonModel\\Attributes\\AsArray;

class GeneratedModelArrayOfMixed
{
    /**
     * @var array<mixed> \$arrayOfMixed
     */
    #[AsArray(itemType: 'mixed')]
    public array \$arrayOfMixed = [];
}

PHP, file_get_contents($this->tmpPath . '/GeneratedModelArrayOfMixed.php'));
    }

    public function testGenerate_array_of_mixed_with_array()
    {
        $json = json_encode([
            'arrayOfMixed' => [1, 'a', true, 3.14, []],
        ]);

        (new JsonGenerator())
            ->namespace('Tests\\Generated')
            ->rootPath($this->tmpPath)
            ->rootModel('GeneratedModelArrayOfMixedWithArray')
            ->generate($json);

        $this->assertSame(<<<PHP
<?php

declare(strict_types=1);

namespace Tests\\Generated;

use Zamaldev\\JsonModel\\Attributes\\AsArray;

class GeneratedModelArrayOfMixedWithArray
{
    /**
     * @var array<mixed> \$arrayOfMixed
     */
    #[AsArray(itemType: 'mixed')]
    public array \$arrayOfMixed = [];
}

PHP, file_get_contents($this->tmpPath . '/GeneratedModelArrayOfMixedWithArray.php'));
    }

    public function testGenerate_array_of_arrays()
    {
        $json = json_encode([
            'arrayOfArrays' => [[1, 2, 3], [4, 5, 6]],
        ]);

        (new JsonGenerator())
            ->namespace('Tests\\Generated')
            ->rootPath($this->tmpPath)
            ->rootModel('GeneratedModelArrayOfArrays')
            ->generate($json);

        $this->assertSame(<<<PHP
<?php

declare(strict_types=1);

namespace Tests\\Generated;

use Zamaldev\\JsonModel\\Attributes\\AsArray;

class GeneratedModelArrayOfArrays
{
    /**
     * @var array<array<int>> \$arrayOfArrays
     */
    #[AsArray(itemType: 'int', level: 2)]
    public array \$arrayOfArrays = [];
}

PHP, file_get_contents($this->tmpPath . '/GeneratedModelArrayOfArrays.php'));
    }

    public function testGenerate_array_of_mixed_arrays()
    {
        $json = json_encode([
            'arrayOfMixedArrays' => [[1, 2, 3], ['a', 'b', 'c']],
        ]);

        (new JsonGenerator())
            ->namespace('Tests\\Generated')
            ->rootPath($this->tmpPath)
            ->rootModel('GeneratedModelArrayOfMixedArrays')
            ->generate($json);

        $this->assertSame(<<<PHP
<?php

declare(strict_types=1);

namespace Tests\\Generated;

use Zamaldev\\JsonModel\\Attributes\\AsArray;

class GeneratedModelArrayOfMixedArrays
{
    /**
     * @var array<array<mixed>> \$arrayOfMixedArrays
     */
    #[AsArray(itemType: 'mixed', level: 2)]
    public array \$arrayOfMixedArrays = [];
}

PHP, file_get_contents($this->tmpPath . '/GeneratedModelArrayOfMixedArrays.php'));
    }

    public function testGenerate_array_of_mixed_arrays_with_array()
    {
        $json = json_encode([
            'arrayOfMixedArraysWithArray' => [[1, 2, 3], ['a', 'b', 'c'], [[]]],
        ]);

        (new JsonGenerator())
            ->namespace('Tests\\Generated')
            ->rootPath($this->tmpPath)
            ->rootModel('GeneratedModelArrayOfMixedArraysWithArray')
            ->generate($json);

        $this->assertSame(<<<PHP
<?php

declare(strict_types=1);

namespace Tests\\Generated;

use Zamaldev\\JsonModel\\Attributes\\AsArray;

class GeneratedModelArrayOfMixedArraysWithArray
{
    /**
     * @var array<array<mixed>> \$arrayOfMixedArraysWithArray
     */
    #[AsArray(itemType: 'mixed', level: 2)]
    public array \$arrayOfMixedArraysWithArray = [];
}

PHP, file_get_contents($this->tmpPath . '/GeneratedModelArrayOfMixedArraysWithArray.php'));
    }

    public function testGenerate_empty_object()
    {
        $json = '{"key":{}}';

        (new JsonGenerator())
            ->namespace('Tests\\Generated')
            ->rootPath($this->tmpPath)
            ->rootModel('GeneratedModelEmptyObject')
            ->generate($json);

        $this->assertSame(<<<PHP
<?php

declare(strict_types=1);

namespace Tests\\Generated;

class GeneratedModelEmptyObject
{
    /**
     * @var ?GeneratedModelEmptyObjectKey \$key
     */
    public ?GeneratedModelEmptyObjectKey \$key = null;
}

PHP, file_get_contents($this->tmpPath . '/GeneratedModelEmptyObject.php'));

        $this->assertSame(<<<PHP
<?php

declare(strict_types=1);

namespace Tests\\Generated;

class GeneratedModelEmptyObjectKey
{
}

PHP, file_get_contents($this->tmpPath . '/GeneratedModelEmptyObjectKey.php'));
    }
}

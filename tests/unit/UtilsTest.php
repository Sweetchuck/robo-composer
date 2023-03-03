<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Composer\Tests\Unit;

use Sweetchuck\Robo\Composer\Test\UnitTester;
use Sweetchuck\Robo\Composer\Utils;
use Codeception\Test\Unit;

/**
 * @covers \Sweetchuck\Robo\Composer\Utils
 */
class UtilsTest extends Unit
{
    protected UnitTester $tester;

    /**
     * @return array
     */
    public function casesFilterEnabled(): array
    {
        return [
            'empty' => [
                [],
                [],
            ],
            'all in' => [
                [
                    'a' => true,
                    'c' => 'foo',
                    'e' => 1,
                    'f' => -1,
                    'h' => [
                        'enabled' => true,
                    ],
                    'j' => (object) [
                        'enabled' => true,
                    ],
                ],
                [
                    'a' => true,
                    'b' => false,
                    'c' => 'foo',
                    'd' => '',
                    'e' => 1,
                    'f' => -1,
                    'g' => 0,
                    'h' => [
                        'enabled' => true,
                    ],
                    'i' => [
                        'enabled' => false,
                    ],
                    'j' => (object) [
                        'enabled' => true,
                    ],
                    'k' => (object) [
                        'enabled' => false,
                    ],
                ],
            ],
            'non-default property' => [
                [
                    'b' => [
                        'available' => true,
                    ],
                    'e' => (object) [
                        'available' => true,
                    ],
                ],
                [
                    'a' => [
                        'enabled' => true,
                    ],
                    'b' => [
                        'available' => true,
                    ],
                    'c' => [
                        'available' => false,
                    ],
                    'd' => (object) [
                        'enabled' => true,
                    ],
                    'e' => (object) [
                        'available' => true,
                    ],
                    'f' => (object) [
                        'available' => false,
                    ],
                ],
                'available',
            ],
        ];
    }

    /**
     * @dataProvider casesFilterEnabled
     */
    public function testFilterEnabled(array $expected, array $items, string $property = 'enabled'): void
    {
        $this->tester->assertEquals($expected, Utils::filterEnabled($items, $property));
    }

    public function casesReplaceFileExtension(): array
    {
        return [
            'basic' => ['a.c', 'a.b', 'c'],
            'schema' => ['foo://a/b/c.e', 'foo://a/b/c.d', 'e'],
        ];
    }

    /**
     * @dataProvider casesReplaceFileExtension
     */
    public function testReplaceFileExtension(string $expected, string $fileName, string $newExtension): void
    {
        $this->tester->assertSame(
            $expected,
            Utils::replaceFileExtension($fileName, $newExtension)
        );
    }

    public function casesRemoveIndirectDependencies(): array
    {
        return [
            'basic' => [
                [
                    'packages' => [
                        ['name' => 'a/a'],
                        ['name' => 'a/b'],
                        ['name' => 'b/a'],
                        ['name' => 'b/b'],
                        ['name' => 'c/a'],
                        ['name' => 'd/a'],
                    ],
                ],
                [
                    'require' => [
                        'a/a' => '*',
                        'a/b' => '*',
                        'd/a' => '*',
                    ],
                    'require-dev' => [
                        'b/a' => '*',
                        'b/b' => '*',
                        'c/a' => '*',
                    ],
                ],
                [
                    'packages' => [
                        ['name' => 'a/a'],
                        ['name' => 'a/b'],
                        ['name' => 'a/c'],
                        ['name' => 'b/a'],
                        ['name' => 'b/b'],
                        ['name' => 'b/c'],
                        ['name' => 'c/a'],
                        ['name' => 'c/b'],
                        ['name' => 'd/a'],
                        ['name' => 'd/b'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider casesRemoveIndirectDependencies
     */
    public function testRemoveIndirectDependencies(array $expected, array $json, array $lock): void
    {
        $this->tester->assertSame($expected, Utils::removeIndirectDependencies($json, $lock));
    }
}

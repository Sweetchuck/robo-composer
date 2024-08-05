<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Composer\Tests\Unit;

use Codeception\Attribute\DataProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use Sweetchuck\Robo\Composer\Tests\UnitTester;
use Sweetchuck\Robo\Composer\Utils;
use Codeception\Test\Unit;

#[CoversClass(Utils::class)]
class UtilsTest extends Unit
{
    protected UnitTester $tester;

    /**
     * @return array<string, mixed>
     */
    public static function casesReplaceFileExtension(): array
    {
        return [
            'basic' => ['a.c', 'a.b', 'c'],
            'schema' => ['foo://a/b/c.e', 'foo://a/b/c.d', 'e'],
        ];
    }

    #[DataProvider('casesReplaceFileExtension')]
    public function testReplaceFileExtension(string $expected, string $fileName, string $newExtension): void
    {
        $this->tester->assertSame(
            $expected,
            Utils::replaceFileExtension($fileName, $newExtension)
        );
    }

    /**
     * @return array<string, mixed>
     */
    public static function casesRemoveIndirectDependencies(): array
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
     * @phpstan-param array<string, mixed> $expected
     * @phpstan-param array<string, mixed> $json
     * @phpstan-param array<string, mixed> $lock
     */
    #[DataProvider('casesRemoveIndirectDependencies')]
    public function testRemoveIndirectDependencies(array $expected, array $json, array $lock): void
    {
        $this->tester->assertSame($expected, Utils::removeIndirectDependencies($json, $lock));
    }
}

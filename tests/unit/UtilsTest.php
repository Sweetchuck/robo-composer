<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Composer\Tests\Unit;

use Sweetchuck\Robo\Composer\Utils;
use Codeception\Test\Unit;

/**
 * @covers \Sweetchuck\Robo\Composer\Utils
 */
class UtilsTest extends Unit
{
    /**
     * @var \Sweetchuck\Robo\Composer\Test\UnitTester
     */
    protected $tester;

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
}

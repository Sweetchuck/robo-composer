<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Composer\Tests\Unit\Filter;

use Sweetchuck\Robo\Composer\Filter\ArrayFilterByPropertyValue;
use Codeception\Test\Unit;

/**
 * @covers \Sweetchuck\Robo\Composer\Filter\ArrayFilterByPropertyValue
 */
class ArrayFilterByPropertyValueTest extends Unit
{
    /**
     * @var \Sweetchuck\Robo\Composer\Test\UnitTester
     */
    protected $tester;

    /**
     * @return array
     */
    public function casesInvoke(): array
    {
        return [
            'empty' => [
                [],
                [],
            ],
            'basic' => [
                [
                    1 => ['name' => 'a/b'],
                    2 => ['name' => 'a/a'],
                ],
                [
                    ['name' => 'b/a'],
                    ['name' => 'a/b'],
                    ['name' => 'a/a'],
                    ['name' => 'c/a'],
                ],
                [
                    'allowedValues' => [
                        'a/a' => '',
                        'a/b' => '',
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider casesInvoke
     */
    public function testInvoke(array $expected, array $items, array $options = []): void
    {
        $filter = new ArrayFilterByPropertyValue();
        $filter->setOptions($options);
        $this->tester->assertSame($expected, array_filter($items, $filter));
    }
}

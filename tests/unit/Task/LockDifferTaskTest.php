<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Composer\Tests\Unit\Task;

use Symfony\Component\Yaml\Yaml;

/**
 * @covers \Sweetchuck\Robo\Composer\Task\LockDifferTask<extended>
 */
class LockDifferTaskTest extends TaskTestBase
{

    /**
     * @return array
     */
    public function casesRunSuccess()
    {
        $cases = [];
        foreach (Yaml::parseFile(codecept_data_dir('lockDiffer/cases.yml')) as $name => $case) {
            $cases[$name] = [
                [
                    'assets' => [
                        'lockDiff' => $case['expected'],
                    ],
                ],
                [
                    'lockA' => $case['lockA'],
                    'lockB' => $case['lockB'],
                ],
            ];
        }

        return $cases;
    }

    /**
     * @dataProvider casesRunSuccess
     */
    public function testRunSuccess(array $expected, array $options): void
    {
        $expected += [
            'wasSuccessful' => true,
            'assets' => [],
        ];

        $result = $this
            ->taskBuilder
            ->taskComposerLockDiffer($options)
            ->run();

        $this->tester->assertSame(
            $expected['wasSuccessful'],
            $result->wasSuccessful(),
            'task exit code'
        );

        $actualAssets = $result->getData();
        foreach ($expected['assets'] as $key => $expectedValue) {
            $this->tester->assertArrayHasKey(
                $key,
                $actualAssets,
                "'$key' asset is present"
            );

            $this->tester->assertSame(
                $expectedValue,
                $actualAssets[$key],
                "$key asset is okay"
            );
        }
    }
}

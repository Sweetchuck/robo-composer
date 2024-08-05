<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Composer\Tests\Unit\Task;

use Codeception\Attribute\DataProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use Sweetchuck\Robo\Composer\Task\LockDifferTask;
use Sweetchuck\Robo\Composer\Task\TaskBase;
use Symfony\Component\Yaml\Yaml;

#[CoversClass(LockDifferTask::class)]
#[CoversClass(TaskBase::class)]
class LockDifferTaskTest extends TaskTestBase
{

    /**
     * @return array<string, mixed>
     */
    public static function casesRunSuccess(): array
    {
        $cases = [];
        foreach (Yaml::parseFile(codecept_data_dir('lockDiffer/cases.yml')) as $name => $case) {
            $cases[(string) $name] = [
                [
                    'assets' => [
                        'composer.lockDiff' => $case['expected'],
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
     * @phpstan-param array<string, mixed> $expected
     * @phpstan-param array<string, mixed> $options
     */
    #[DataProvider('casesRunSuccess')]
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

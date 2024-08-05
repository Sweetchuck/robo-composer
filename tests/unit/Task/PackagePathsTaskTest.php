<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Composer\Tests\Unit\Task;

use Codeception\Attribute\DataProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use Sweetchuck\Codeception\Module\RoboTaskRunner\DummyProcess;
use Sweetchuck\Robo\Composer\Task\CliTaskBase;
use Sweetchuck\Robo\Composer\Task\PackagePathsTask;
use Sweetchuck\Robo\Composer\Task\TaskBase;

#[CoversClass(PackagePathsTask::class)]
#[CoversClass(CliTaskBase::class)]
#[CoversClass(TaskBase::class)]
class PackagePathsTaskTest extends TaskTestBase
{

    /**
     * @return array<string, mixed>
     */
    public static function casesGetCommand(): array
    {
        return [
            'defaults' => [
                'composer show -P',
                [],
            ],
            'working directory' => [
                "cd 'my-wd' && composer show -P",
                [
                    'workingDirectory' => 'my-wd',
                ],
            ],
            'composer' => [
                'composer.phar show -P',
                [
                    'composerExecutable' => 'composer.phar',
                ],
            ],
            'wd+composer' => [
                "cd 'my-wd' && composer.phar show -P",
                [
                    'workingDirectory' => 'my-wd',
                    'composerExecutable' => 'composer.phar',
                ],
            ],
            'wd+envVarComposer' => [
                "cd 'my-wd' && COMPOSER='foo.json' composer show -P",
                [
                    'workingDirectory' => 'my-wd',
                    'envVarComposer' => 'foo.json',
                ],
            ],
        ];
    }

    /**
     * @phpstan-param array<string, mixed> $options
     */
    #[DataProvider('casesGetCommand')]
    public function testGetCommand(string $expected, array $options): void
    {
        $task = $this->taskBuilder->taskComposerPackagePaths($options);
        $this->tester->assertEquals($expected, $task->getCommand());
    }

    /**
     * @return array<string, mixed>
     */
    public static function casesRunSuccess(): array
    {
        return [
            'empty' => [
                [
                    'assets' => [
                        'composer.packagePaths' => [],
                    ],
                ],
                [],
                [
                    [
                        'exitCode' => 0,
                        'stdOutput' => '',
                        'stdError' => '',
                    ],
                ],
            ],
            'one line' => [
                [
                    'assets' => [
                        'composer.packagePaths' => [
                            'a/b' => 'c',
                        ],
                    ],
                ],
                [],
                [
                    [
                        'exitCode' => 0,
                        'stdOutput' => implode("\n", [
                            'a/b c',
                            ''
                        ]),
                        'stdError' => '',
                    ],
                ],
            ],
            'more lines with trailing space' => [
                [
                    'assets' => [
                        'myPrefix01.composer.packagePaths' => [
                            'a/b' => 'c',
                            'd/e' => 'f ',
                        ],
                    ],
                ],
                [
                    'assetNamePrefix' => 'myPrefix01.',
                ],
                [
                    [
                        'exitCode' => 0,
                        'stdOutput' => implode("\n", [
                            'a/b c',
                            'd/e f ',
                            ''
                        ]),
                        'stdError' => '',
                    ],
                ],
            ],
        ];
    }

    /**
     * @phpstan-param array<string, mixed> $expected
     * @phpstan-param array<string, mixed> $options
     * @phpstan-param array<string, mixed> $processProphecy
     */
    #[DataProvider('casesRunSuccess')]
    public function testRunSuccess(array $expected, array $options, array $processProphecy): void
    {
        $expected += [
            'wasSuccessful' => true,
            'assets' => [],
        ];

        DummyProcess::$prophecy = $processProphecy;

        $result = $this
            ->taskBuilder
            ->taskComposerPackagePaths($options)
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

    public function testRunFail(): void
    {
        DummyProcess::$prophecy = [
            [
                'exitCode' => 42,
                'stdOutput' => '',
                'stdError' => 'my error message',
            ],
        ];
        $task = $this->taskBuilder->taskComposerPackagePaths();

        $result = $task->run();

        $this->tester->assertSame(42, $result->getExitCode());
        $this->tester->assertSame('my error message', $result->getMessage());
    }
}

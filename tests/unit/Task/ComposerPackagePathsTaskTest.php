<?php

namespace Sweetchuck\Robo\Composer\Tests\Unit\Robo\Task;

use ReflectionClass;
use Sweetchuck\Robo\Composer\Task\ComposerPackagePathsTask;
use Sweetchuck\Robo\Composer\Test\Helper\Dummy\Process as DummyProcess;
use Codeception\Test\Unit;
use Codeception\Util\Stub;
use Robo\Robo;

/**
 * @covers \Sweetchuck\Robo\Composer\Task\ComposerPackagePathsTask<extended>
 */
class ComposerPackagePathsTaskTest extends Unit
{
    /**
     * @var \Sweetchuck\Robo\Composer\Test\UnitTester
     */
    protected $tester;

    /**
     * @return array
     */
    public function casesGetCommand()
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
     * @dataProvider casesGetCommand
     */
    public function testGetCommand(string $expected, array $options): void
    {
        $task = new ComposerPackagePathsTask($options);
        $this->tester->assertEquals($expected, $task->getCommand());
    }

    /**
     * @return array
     */
    public function casesParseOutput()
    {
        return [
            'empty' => [
                [
                    'packagePaths' => [],
                ],
                [],
                [
                    'processStdOutput' => '',
                ],
            ],
            'one line' => [
                [
                    'packagePaths' => [
                        'a/b' => 'c',
                    ],
                ],
                [],
                [
                    'processStdOutput' => implode("\n", [
                        'a/b c',
                        ''
                    ]),
                ],
            ],
            'more lines with trailing space' => [
                [
                    'myPrefix01packagePaths' => [
                        'a/b' => 'c',
                        'd/e' => 'f ',
                    ],
                ],
                [
                    'assetNamePrefix' => 'myPrefix01',
                ],
                [
                    'processStdOutput' => implode("\n", [
                        'a/b c',
                        'd/e f ',
                        ''
                    ]),
                ],
            ],
        ];
    }

    /**
     * @dataProvider casesParseOutput
     */
    public function testParseOutput(array $expected, array $options, array $propetyValues): void
    {
        /** @var \Sweetchuck\Robo\Composer\Task\ComposerPackagePathsTask $task */
        $task = Stub::construct(ComposerPackagePathsTask::class, [$options], $propetyValues);

        $class = new ReflectionClass(ComposerPackagePathsTask::class);
        $parseOutputMethod = $class->getMethod('parseOutput');
        $parseOutputMethod->setAccessible(true);
        $assetsProperty = $class->getProperty('assets');
        $assetsProperty->setAccessible(true);

        $parseOutputMethod->invokeArgs($task, []);

        $this->tester->assertEquals($expected, $assetsProperty->getValue($task));
    }

    /**
     * @return array
     */
    public function casesRunSuccess(): array
    {
        return [
            'empty' => [
                [],
            ],
            'simple' => [
                [
                    'a/b' => 'c',
                ],
            ],
        ];
    }

    /**
     * @dataProvider casesRunSuccess
     */
    public function testRunSuccess(array $expected): void
    {
        $fakeStdOutput = '';
        foreach ($expected as $packageName => $packagePath) {
            $fakeStdOutput .= "$packageName $packagePath\n";
        }

        $container = Robo::createDefaultContainer();
        Robo::setContainer($container);

        /** @var \Sweetchuck\Robo\Composer\Task\ComposerPackagePathsTask $task */
        $task = Stub::construct(
            ComposerPackagePathsTask::class,
            [
                [
                    'workingDirectory' => 'my-wd',
                    'assetNamePrefix' => 'myPrefix01.',
                ],
            ],
            [
                'processClass' => DummyProcess::class,
            ]
        );

        $processIndex = count(DummyProcess::$instances);
        DummyProcess::$prophecy[$processIndex] = [
            'exitCode' => 0,
            'stdOutput' => $fakeStdOutput,
            'stdError' => '',
        ];

        $result = $task->run();

        $this->assertEquals(
            $expected,
            $result['myPrefix01.packagePaths'],
            'Package paths in the task result'
        );
    }

    public function testRunFail(): void
    {
        $container = Robo::createDefaultContainer();
        Robo::setContainer($container);

        /** @var \Sweetchuck\Robo\Composer\Task\ComposerPackagePathsTask $task */
        $task = Stub::construct(
            ComposerPackagePathsTask::class,
            [],
            [
                'processClass' => DummyProcess::class,
            ]
        );

        $processIndex = count(DummyProcess::$instances);
        DummyProcess::$prophecy[$processIndex] = [
            'exitCode' => 1,
            'stdOutput' => '',
            'stdError' => '',
        ];

        $result = $task->run();

        $this->assertEquals(1, $result->getExitCode());
    }
}

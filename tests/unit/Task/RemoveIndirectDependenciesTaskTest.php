<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Composer\Tests\Unit\Task;

use Codeception\Attribute\DataProvider;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\Attributes\CoversClass;
use Sweetchuck\Robo\Composer\Task\RemoveIndirectDependenciesTask;
use Sweetchuck\Robo\Composer\Task\TaskBase;
use Symfony\Component\Filesystem\Path;

#[CoversClass(RemoveIndirectDependenciesTask::class)]
#[CoversClass(TaskBase::class)]
class RemoveIndirectDependenciesTaskTest extends TaskTestBase
{

    /**
     * @return array<string, mixed>
     */
    public static function casesRunSuccess(): array
    {
        return [
            'basic' => [
                [
                    'lockFileExists' => true,
                    'lockFileName' => 'composer.lock',
                    'lock' => [
                        'packages' => [
                            ['name' => 'a/a'],
                            ['name' => 'a/b'],
                            ['name' => 'b/a'],
                            ['name' => 'b/b'],
                            ['name' => 'c/a'],
                            ['name' => 'd/a'],
                        ],
                    ],
                ],
                [
                    'composer.json' => json_encode([
                        'name' => 'v/p',
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
                    ]),
                    'composer.lock' => json_encode([
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
                    ]),
                ],
                [],
            ],
        ];
    }

    /**
     * @phpstan-param array<string, mixed> $expected
     * @phpstan-param array<string, mixed> $vfsStructure
     * @phpstan-param array<string, mixed> $options
     */
    #[DataProvider('casesRunSuccess')]
    public function testRunSuccess(array $expected, array $vfsStructure, array $options): void
    {
        $expected += [
            'exitCode' => 0,
            'lockFileExists' => true,
            'lockFileName' => 'composer.lock',
        ];

        $vfs = vfsStream::setup(__FUNCTION__, null, $vfsStructure);
        $rootDir = $vfs->url();

        $options += ['workingDirectory' => $rootDir];

        $result = $this
            ->taskBuilder
            ->taskComposerRemoveIndirectDependencies($options)
            ->run();

        $this->tester->assertSame($expected['exitCode'], $result->getExitCode());

        $lockFileName = Path::join($rootDir, $expected['lockFileName']);
        $expected['lockFileExists'] ?
            $this->tester->assertFileExists($lockFileName)
            : $this->tester->assertFileNotExists($lockFileName);

        if ($expected['lockFileExists']) {
            $this->tester->assertSame(
                $expected['lock'],
                json_decode(file_get_contents($lockFileName) ?: '{}', true)
            );
        }
    }
}

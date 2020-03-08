<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Composer\Tests\Unit\Task;

use org\bovigo\vfs\vfsStream;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @covers \Sweetchuck\Robo\Composer\Task\RemoveIndirectDependenciesTask<extended>
 */
class ComposerRemoveIndirectDependenciesTaskTest extends TaskTestBase
{

    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    protected $rootDir;

    /**
     * @inheritdoc
     */
    public function _before()
    {
        parent::_before();

        $this->rootDir = vfsStream::setup('ComposerRemoveIndirectDependenciesTaskTest');
    }

    /**
     * @inheritdoc
     */
    protected function _after()
    {
        (new Filesystem())->remove($this->rootDir->getName());
        $this->rootDir = null;

        parent::_after();
    }

    /**
     * @return array
     */
    public function casesRunSuccess()
    {
        return [
            'basic' => [
                [
                    'files' => [
                        'composer.lock' => implode("\n", [
                            '{',
                            '    "packages": [',
                            '        {',
                            '            "name": "a/a"',
                            '        },',
                            '        {',
                            '            "name": "b/a"',
                            '        },',
                            '        {',
                            '            "name": "b/b"',
                            '        },',
                            '        {',
                            '            "name": "c/a"',
                            '        }',
                            '    ],',
                            '    "packages-dev": [',
                            '        {',
                            '            "name": "d/a"',
                            '        },',
                            '        {',
                            '            "name": "e/a"',
                            '        },',
                            '        {',
                            '            "name": "f/a"',
                            '        },',
                            '        {',
                            '            "name": "g/a"',
                            '        }',
                            '    ]',
                            '}',
                        ]),
                    ],
                ],
                [],
                [
                    'composer.json' => json_encode([
                        'require' => [
                            'a/a' => '1',
                            'b/a' => '1',
                            'b/b' => '1',
                            'c/a' => '1',
                        ],
                        'require-dev' => [
                            'd/a' => '1',
                            'e/a' => '1',
                            'f/a' => '1',
                            'g/a' => '1',
                        ],
                    ]),
                    'composer.lock' => json_encode([
                        'packages' => [
                            ['name' => 'a/a'],
                            ['name' => 'a/b'],
                            ['name' => 'b/a'],
                            ['name' => 'b/b'],
                            ['name' => 'b/c'],
                            ['name' => 'c/a'],
                            ['name' => 'c/b'],
                        ],
                        'packages-dev' => [
                            ['name' => 'd/a'],
                            ['name' => 'd/b'],
                            ['name' => 'e/a'],
                            ['name' => 'e/b'],
                            ['name' => 'f/a'],
                            ['name' => 'f/b'],
                            ['name' => 'g/a'],
                            ['name' => 'g/b'],
                        ],
                    ]),
                ],
            ],
        ];
    }

    /**
     * @dataProvider casesRunSuccess
     */
    public function testRunSuccess(array $expected, array $options, array $files): void
    {
        $baseDir = $this->rootDir->url();
        $options += ['workingDirectory' => $baseDir];
        foreach ($files as $fileName => $fileContent) {
            file_put_contents("$baseDir/$fileName", $fileContent);
        }

        $expected += [
            'wasSuccessful' => true,
            'assets' => [],
        ];

        $result = $this
            ->taskBuilder
            ->taskComposerRemoveIndirectDependencies($options)
            ->run();

        $this->tester->assertSame(
            $expected['wasSuccessful'],
            $result->wasSuccessful(),
            'task exit code'
        );

        foreach ($expected['files'] as $expectedFileName => $expectedFileContent) {
            $this->tester->assertSame(
                $expectedFileContent,
                file_get_contents("$baseDir/$expectedFileName"),
                "file content of '$expectedFileName'"
            );
        }
    }
}

<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Composer\Tests\Acceptance\Task;

use Codeception\Example;
use org\bovigo\vfs\vfsStream;
use Sweetchuck\Robo\composer\Test\AcceptanceTester;
use Sweetchuck\Robo\Composer\Test\Helper\RoboFiles\ComposerRoboFile;
use Webmozart\PathUtil\Path;

class RemoveIndirectDependenciesCest
{
    /**
     * @var string
     */
    protected $class = ComposerRoboFile::class;

    protected function id(string $suffix): string
    {
        return static::class . ":$suffix";
    }

    public function casesRemoveIndirectDependencies(): array
    {
        return [
            'basic' => [
                'id' => 'basic',
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
                'vfsStructure' => [
                    'composer.json' => json_encode([
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
                'options' => [],
            ],
        ];
    }

    /**
     * @dataProvider casesRemoveIndirectDependencies
     */
    public function runRemoveIndirectDependencies(AcceptanceTester $I, Example $example)
    {
        $vfs = vfsStream::setup(
            __FUNCTION__,
            null,
            $example['vfsStructure']
        );
        $wd = $vfs->url();

        $options = $example['options'];
        $options[] = "--workingDirectory=$wd";

        $lockFileName = Path::join($wd, $example['lockFileName']);

        $id = $this->id($example['id']);
        $I->runRoboTask(
            $id,
            $this->class,
            'composer:remove-indirect-dependencies',
            ...$options
        );

        $I->assertSame(0, $I->getRoboTaskExitCode($id));

        $example['lockFileExists'] ?
            $I->assertFileExists($lockFileName)
            : $I->assertFileNotExists($lockFileName);

        if ($example['lockFileExists']) {
            $I->assertSame(
                $example['lock'],
                json_decode(file_get_contents($lockFileName), true),
                "content of the $lockFileName file"
            );
        }
    }
}

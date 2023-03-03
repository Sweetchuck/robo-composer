<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Composer\Tests\Acceptance\Task;

use Codeception\Example;
use Sweetchuck\Robo\composer\Test\AcceptanceTester;
use Sweetchuck\Robo\Composer\Test\Helper\RoboFiles\ComposerRoboFile;
use Symfony\Component\Yaml\Yaml;

class LockDifferTaskCest
{
    protected string $class = ComposerRoboFile::class;

    protected function id(string $suffix): string
    {
        return static::class . ":$suffix";
    }

    protected function casesLockDiffSuccess(): array
    {
        $cases = [];
        foreach (Yaml::parseFile(codecept_data_dir('lockDiffer/cases.yml')) as $name => $case) {
            $cases[$name] = [
                'name' => $name,
                'expected' => Yaml::dump($case['expected'], 99, 4),
                'lockA' => $this->textToFileName(json_encode($case['lockA'])),
                'lockB' => $this->textToFileName(json_encode($case['lockB'])),
            ];
        }

        return $cases;
    }

    /**
     * @dataProvider casesLockDiffSuccess
     */
    public function runLockDiffSuccess(AcceptanceTester $I, Example $example): void
    {
        $id = $this->id("composer:lock-diff:{$example['name']}");
        $I->runRoboTask(
            $id,
            $this->class,
            'composer:lock-diff',
            $example['lockA'],
            $example['lockB']
        );

        $I->assertSame(
            0,
            $I->getRoboTaskExitCode($id),
            'exitCode'
        );

        $I->assertSame(
            " [Composer - lock differ] \n",
            $I->getRoboTaskStdError($id),
            'stdError'
        );

        $I->assertEquals(
            $example['expected'],
            $I->getRoboTaskStdOutput($id),
            'stdOutput'
        );
    }

    protected function textToFileName(string $text): string
    {
        return 'data://text/plain;base64,' . base64_encode($text);
    }
}

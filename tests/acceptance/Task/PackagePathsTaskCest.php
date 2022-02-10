<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Composer\Tests\Acceptance\Task;

use Sweetchuck\Robo\composer\Test\AcceptanceTester;
use Sweetchuck\Robo\Composer\Test\Helper\RoboFiles\ComposerRoboFile;

class PackagePathsTaskCest
{
    protected string $class = ComposerRoboFile::class;

    protected function id(string $suffix): string
    {
        return static::class . ":$suffix";
    }

    public function runPackagePathsBasicSuccess(AcceptanceTester $I)
    {
        $id = $this->id('package-paths:basic:composer');
        $I->runRoboTask($id, $this->class, 'package-paths:basic', 'composer');
        $I->assertSame(0, $I->getRoboTaskExitCode($id));
        $I->assertSame("Success\n", $I->getRoboTaskStdOutput($id));
    }

    public function runPackagePathsBasicFail(AcceptanceTester $I)
    {
        $id = $this->id('package-paths:basic:false');
        $I->runRoboTask($id, $this->class, 'package-paths:basic', 'false');
        $I->assertSame(1, $I->getRoboTaskExitCode($id));
        $I->assertStringContainsString("\nFail\n", $I->getRoboTaskStdError($id));
    }
}

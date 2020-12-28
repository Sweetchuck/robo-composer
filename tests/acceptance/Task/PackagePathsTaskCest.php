<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Composer\Tests\Acceptance\Task;

use Sweetchuck\Robo\composer\Test\AcceptanceTester;
use Sweetchuck\Robo\Composer\Test\Helper\RoboFiles\ComposerRoboFile;

class PackagePathsTaskCest
{
    /**
     * @var string
     */
    protected $class = ComposerRoboFile::class;

    protected function id(string $suffix): string
    {
        return static::class . ":$suffix";
    }

    public function runPackagePathsBasicSuccess(AcceptanceTester $I)
    {
        $id = $this->id('package-paths:basic:composer');
        $I->runRoboTask($id, $this->class, 'package-paths:basic', 'composer');
        $I->assertEquals(0, $I->getRoboTaskExitCode($id));
        $I->assertEquals("Success\n", $I->getRoboTaskStdOutput($id));
    }

    public function runPackagePathsBasicFail(AcceptanceTester $I)
    {
        $id = $this->id('package-paths:basic:false');
        $I->runRoboTask($id, $this->class, 'package-paths:basic', 'false');
        $I->assertEquals(1, $I->getRoboTaskExitCode($id));
        $I->assertStringContainsString("Fail\n", $I->getRoboTaskStdError($id));
    }
}

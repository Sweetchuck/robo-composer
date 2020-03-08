<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Composer\Tests\Acceptance\Task;

use Sweetchuck\Robo\composer\Test\AcceptanceTester;

class ComposerPackagePathsTaskCest extends TaskCestBase
{
    public function runComposerPackagePathsSuccess(AcceptanceTester $I)
    {
        $id = $this->id('success');
        $I->runRoboTask($id, $this->class, 'composer:package-paths', 'composer');
        $I->assertEquals(0, $I->getRoboTaskExitCode($id));
        $I->assertEquals("Success\n", $I->getRoboTaskStdOutput($id));
    }

    public function runComposerPackagePathsFail(AcceptanceTester $I)
    {
        $id = $this->id('fail');
        $I->runRoboTask($id, $this->class, 'composer:package-paths', 'false');
        $I->assertEquals(1, $I->getRoboTaskExitCode($id));
        $I->assertStringContainsString("Fail\n", $I->getRoboTaskStdError($id));
    }
}

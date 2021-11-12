<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Composer\Tests\Unit;

use Codeception\Test\Unit;
use Sweetchuck\Robo\Composer\LockDiffer;
use Sweetchuck\Robo\Composer\Test\UnitTester;
use Symfony\Component\Yaml\Yaml;

/**
 * @covers \Sweetchuck\Robo\Composer\LockDiffer
 */
class LockDifferTest extends Unit
{

    protected UnitTester $tester;

    public function casesDiff(): array
    {
        return Yaml::parseFile(codecept_data_dir('lockDiffer/cases.yml'));
    }

    /**
     * @dataProvider casesDiff
     */
    public function testDiff(array $expected, array $a, array $b)
    {
        $lockDiffer = new LockDiffer();
        $this->tester->assertSame($expected, $lockDiffer->diff($a, $b));
    }
}

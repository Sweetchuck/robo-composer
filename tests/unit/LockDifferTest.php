<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Composer\Tests\Unit;

use Codeception\Attribute\DataProvider;
use Codeception\Test\Unit;
use PHPUnit\Framework\Attributes\CoversClass;
use Sweetchuck\Robo\Composer\LockDiffer;
use Sweetchuck\Robo\Composer\Tests\UnitTester;
use Symfony\Component\Yaml\Yaml;

#[CoversClass(LockDiffer::class)]
class LockDifferTest extends Unit
{

    protected UnitTester $tester;

    /**
     * @return array<string, mixed>
     */
    public static function casesDiff(): array
    {
        return Yaml::parseFile(codecept_data_dir('lockDiffer/cases.yml'));
    }

    /**
     * @phpstan-param array<string, mixed> $expected
     * @phpstan-param array<string, mixed> $lockA
     * @phpstan-param array<string, mixed> $lockB
     */
    #[DataProvider('casesDiff')]
    public function testDiff(array $expected, array $lockA, array $lockB): void
    {
        $lockDiffer = new LockDiffer();
        $this->tester->assertSame($expected, $lockDiffer->diff($lockA, $lockB));
    }
}

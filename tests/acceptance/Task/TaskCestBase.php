<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Composer\Tests\Acceptance\Task;

use Sweetchuck\Robo\Composer\Test\Helper\RoboFiles\ComposerRoboFile;

class TaskCestBase
{
    /**
     * @var string
     */
    protected $class = ComposerRoboFile::class;

    protected function id(string $suffix): string
    {
        return static::class . ":$suffix";
    }
}

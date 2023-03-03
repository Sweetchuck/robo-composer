<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Composer\Task;

use Sweetchuck\Robo\Composer\LockDiffer;

class LockDifferTask extends TaskBase
{

    protected string $taskName = 'Composer - lock differ';

    // region lockA
    protected array $lockA = [];

    public function getLockA(): array
    {
        return $this->lockA;
    }

    public function setLockA(array $lockA): static
    {
        $this->lockA = $lockA;

        return $this;
    }
    // endregion

    // region lockB
    protected array $lockB = [];

    public function getLockB(): array
    {
        return $this->lockB;
    }

    public function setLockB(array $lockB): static
    {
        $this->lockB = $lockB;

        return $this;
    }
    // endregion

    public function setOptions(array $options): static
    {
        parent::setOptions($options);

        if (array_key_exists('lockA', $options)) {
            $this->setLockA($options['lockA']);
        }

        if (array_key_exists('lockB', $options)) {
            $this->setLockB($options['lockB']);
        }

        return $this;
    }

    protected function runDoIt(): static
    {
        $this->assets['composer.lockDiff'] = (new LockDiffer())->diff($this->getLockA(), $this->getLockB());

        return $this;
    }
}

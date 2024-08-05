<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Composer\Task;

use Sweetchuck\Robo\Composer\LockDiffer;

class LockDifferTask extends TaskBase
{

    protected string $taskName = 'Composer - lock differ';

    // region lockA
    /**
     * @var array<string, mixed>
     */
    protected array $lockA = [];

    /**
     * @return array<string, mixed>
     */
    public function getLockA(): array
    {
        return $this->lockA;
    }

    /**
     * @param array<string, mixed> $lockA
     */
    public function setLockA(array $lockA): static
    {
        $this->lockA = $lockA;

        return $this;
    }
    // endregion

    // region lockB
    /**
     * @var array<string, mixed>
     */
    protected array $lockB = [];

    /**
     * @return array<string, mixed>
     */
    public function getLockB(): array
    {
        return $this->lockB;
    }

    /**
     * @param array<string, mixed> $lockB
     */
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

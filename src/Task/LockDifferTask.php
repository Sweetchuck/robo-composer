<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Composer\Task;

use Sweetchuck\Robo\Composer\LockDiffer;

class LockDifferTask extends TaskBase
{

    /**
     * {@inheritdoc}
     */
    protected $taskName = 'Composer - lock differ';

    // region lockA
    /**
     * @var array
     */
    protected $lockA = [];

    public function getLockA(): array
    {
        return $this->lockA;
    }

    /**
     * @return $this
     */
    public function setLockA(array $lockA)
    {
        $this->lockA = $lockA;

        return $this;
    }
    // endregion

    // region lockB
    /**
     * @var array
     */
    protected $lockB = [];

    public function getLockB(): array
    {
        return $this->lockB;
    }

    /**
     * @return $this
     */
    public function setLockB(array $lockB)
    {
        $this->lockB = $lockB;

        return $this;
    }
    // endregion

    /**
     * {@inheritdoc}
     */
    public function setOptions(array $options)
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

    protected function runDoIt()
    {
        $this->assets['composer.lockDiff'] = (new LockDiffer())->diff($this->getLockA(), $this->getLockB());

        return $this;
    }
}

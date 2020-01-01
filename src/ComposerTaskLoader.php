<?php

namespace Sweetchuck\Robo\Composer;

use League\Container\ContainerAwareInterface;
use Robo\Collection\CollectionBuilder;

trait ComposerTaskLoader
{
    /**
     * @return \Sweetchuck\Robo\Composer\Task\ComposerPackagePathsTask|\Robo\Collection\CollectionBuilder
     */
    protected function taskComposerPackagePaths(array $options = []): CollectionBuilder
    {
        /** @var \Sweetchuck\Robo\Composer\Task\ComposerPackagePathsTask $task */
        $task = $this->task(Task\ComposerPackagePathsTask::class, $options);
        if ($this instanceof ContainerAwareInterface) {
            $container = $this->getContainer();
            if ($container) {
                $task->setContainer($this->getContainer());
            }
        }

        return $task;
    }
}

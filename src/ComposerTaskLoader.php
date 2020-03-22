<?php

declare(strict_types = 1);

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
        $task = $this->task(Task\ComposerPackagePathsTask::class);
        if ($this instanceof ContainerAwareInterface) {
            $container = $this->getContainer();
            if ($container) {
                $task->setContainer($this->getContainer());
            }
        }

        $task->setOptions($options);

        return $task;
    }
    /**
     * @return \Sweetchuck\Robo\Composer\Task\RemoveIndirectDependencies|\Robo\Collection\CollectionBuilder
     */
    protected function taskComposerRemoveIndirectDependencies(array $options = []): CollectionBuilder
    {
        /** @var \Sweetchuck\Robo\Composer\Task\RemoveIndirectDependencies $task */
        $task = $this->task(Task\RemoveIndirectDependencies::class);
        $task->setOptions($options);

        return $task;
    }
}

<?php

namespace Sweetchuck\Robo\Composer;

use Robo\Collection\CollectionBuilder;

trait ComposerTaskLoader
{
    /**
     * @return \Sweetchuck\Robo\Composer\Task\ComposerPackagePathsTask|\Robo\Collection\CollectionBuilder
     */
    protected function taskComposerPackagePaths(array $options = []): CollectionBuilder
    {
        return $this->task(Task\ComposerPackagePathsTask::class, $options);
    }
}

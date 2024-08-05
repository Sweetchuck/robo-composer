<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Composer\Task;

use Robo\Result;
use Robo\Task\BaseTask;
use Robo\TaskInfo;

abstract class TaskBase extends BaseTask
{

    /**
     * @var array<string, mixed>
     */
    protected array $assets = [];

    protected string $taskName = '';

    public function getTaskName(): string
    {
        return $this->taskName ?: TaskInfo::formatTaskName($this);
    }

    /**
     * {@inheritdoc}
     *
     * @phpstan-param null|array<string, mixed> $context
     *
     * @phpstan-return array<string, mixed>
     */
    protected function getTaskContext($context = null)
    {
        if (!$context) {
            $context = [];
        }

        if (empty($context['name'])) {
            $context['name'] = $this->getTaskName();
        }

        return parent::getTaskContext($context);
    }

    // region Option - assetNamePrefix.
    protected string $assetNamePrefix = '';

    public function getAssetNamePrefix(): string
    {
        return $this->assetNamePrefix;
    }

    public function setAssetNamePrefix(string $value): static
    {
        $this->assetNamePrefix = $value;

        return $this;
    }
    //endregion

    /**
     * @param array<string, mixed> $options
     */
    public function setOptions(array $options): static
    {
        if (array_key_exists('assetNamePrefix', $options)) {
            $this->setAssetNamePrefix($options['assetNamePrefix']);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        return $this
            ->runHeader()
            ->runDoIt()
            ->runPrepareAssets()
            ->runReturn();
    }

    protected function runHeader(): static
    {
        $this->printTaskInfo('');

        return $this;
    }

    abstract protected function runDoIt(): static;

    protected function runPrepareAssets(): static
    {
        return $this;
    }

    protected function runReturn(): Result
    {
        return new Result(
            $this,
            $this->getTaskResultCode(),
            $this->getTaskResultMessage(),
            $this->getAssetsWithPrefixedNames()
        );
    }

    protected function getTaskResultCode(): int
    {
        return 0;
    }

    protected function getTaskResultMessage(): string
    {
        return '';
    }

    /**
     * @return array<string, mixed>
     */
    protected function getAssetsWithPrefixedNames(): array
    {
        $prefix = $this->getAssetNamePrefix();
        if (!$prefix) {
            return $this->assets;
        }

        $assets = [];
        foreach ($this->assets as $key => $value) {
            $assets["{$prefix}{$key}"] = $value;
        }

        return $assets;
    }
}

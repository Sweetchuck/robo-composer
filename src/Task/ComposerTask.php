<?php

namespace Sweetchuck\Robo\Composer\Task;

use Robo\Contract\CommandInterface;
use Robo\Result;
use Robo\Task\BaseTask;
use Robo\TaskInfo;
use Sweetchuck\Robo\Composer\Utils;
use Symfony\Component\Process\Process;

abstract class ComposerTask extends BaseTask
{
    /**
     * @var array
     */
    protected $assets = [];

    /**
     * @var string
     */
    protected $processClass = Process::class;

    /**
     * @var \Symfony\Component\Process\Process
     */
    protected $process;

    /**
     * @var int
     */
    protected $processExitCode = 0;

    /**
     * @var string
     */
    protected $processStdOutput = '';

    /**
     * @var string
     */
    protected $processStdError = '';

    /**
     * @var string
     */
    protected $taskName = '';

    public function getTaskName(): string
    {
        return $this->taskName ?: TaskInfo::formatTaskName($this);
    }

    /**
     * {@inheritdoc}
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

    //region Options.

    //region Option - workingDirectory
    /**
     * @var string
     */
    public $workingDirectory = '';

    public function getWorkingDirectory(): string
    {
        return $this->workingDirectory;
    }

    /**
     * @return $this
     */
    public function setWorkingDirectory(string $workingDirectory)
    {
        $this->workingDirectory = $workingDirectory;

        return $this;
    }
    // endregion

    //region Option - composerExecutable
    /**
     * @var string
     */
    public $composerExecutable = 'composer';

    public function getComposerExecutable(): string
    {
        return $this->composerExecutable;
    }

    /**
     * @return $this
     */
    public function setComposerExecutable(string $composerExecutable)
    {
        $this->composerExecutable = $composerExecutable;

        return $this;
    }
    //endregion

    // region Option - assetNamePrefix.
    /**
     * @var string
     */
    protected $assetNamePrefix = '';

    public function getAssetNamePrefix(): string
    {
        return $this->assetNamePrefix;
    }

    /**
     * @return $this
     */
    public function setAssetNamePrefix(string $value)
    {
        $this->assetNamePrefix = $value;

        return $this;
    }
    //endregion

    //region Option - envVarComposer.
    /**
     * @var null|string
     */
    protected $envVarComposer = null;

    public function getEnvVarComposer(): ?string
    {
        return $this->envVarComposer;
    }

    /**
     * @return $this
     */
    public function setEnvVarComposer(?string $value)
    {
        $this->envVarComposer = $value;

        return $this;
    }
    //endregion

    //endregion

    public function __construct(array $options = [])
    {
        $this->setOptions($options);
    }

    /**
     * @return $this
     */
    public function setOptions(array $options)
    {
        foreach ($options as $name => $value) {
            switch ($name) {
                case 'envVarComposer':
                    $this->setEnvVarComposer($value);
                    break;

                case 'workingDirectory':
                    $this->setWorkingDirectory($value);
                    break;

                case 'composerExecutable':
                    $this->setComposerExecutable($value);
                    break;

                case 'assetNamePrefix':
                    $this->setAssetNamePrefix($value);
                    break;
            }
        }

        return $this;
    }

    protected function getAction(): string
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function run(): Result
    {
        $command = $this->getCommand();
        $this->printTaskInfo($command);
        $this->process = new $this->processClass($command);

        $this->processExitCode = $this->process->run();
        $this->processStdOutput = $this->process->getOutput();
        $this->processStdError = $this->process->getErrorOutput();
        if ($this->processExitCode) {
            return new Result(
                $this,
                $this->processExitCode,
                $this->processStdError,
                $this->assets
            );
        }

        $this->assets['workingDirectory'] = $this->getWorkingDirectory();
        $this->parseOutput();

        return Result::success($this, '', $this->assets);
    }

    /**
     * @return $this
     */
    protected function parseOutput()
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCommand(): string
    {
        $envPattern = [];
        $envArgs = [];

        $cmdPattern = [];
        $cmdArgs = [];

        $cmdAsIs = [];

        $cmdPattern[] = '%s';
        $cmdArgs[] = escapeshellcmd($this->getComposerExecutable());

        $action = $this->getAction();
        if ($action) {
            $cmdPattern[] = $action;
        }

        foreach ($this->getCommandOptions() as $optionName => $option) {
            switch ($option['type']) {
                case 'environment':
                    if ($option['value'] !== null) {
                        $optionName = $option['name'] ?? $optionName;
                        $envPattern[] = "{$optionName}=%s";
                        $envArgs[] = escapeshellarg($option['value']);
                    }
                    break;

                case 'value':
                    if ($option['value']) {
                        $cmdPattern[] = "--$optionName=%s";
                        $cmdArgs[] = escapeshellarg($option['value']);
                    }
                    break;

                case 'value-optional':
                    if ($option['value'] !== null) {
                        $value = (string) $option['value'];
                        if ($value === '') {
                            $cmdPattern[] = "--{$optionName}";
                        } else {
                            $cmdPattern[] = "--{$optionName}=%s";
                            $cmdArgs[] = escapeshellarg($value);
                        }
                    }
                    break;

                case 'flag':
                    if ($option['value']) {
                        $cmdPattern[] = "--$optionName";
                    }
                    break;

                case 'tri-state':
                    if ($option['value'] !== null) {
                        $cmdPattern[] = $option['value'] ? "--$optionName" : "--no-$optionName";
                    }
                    break;

                case 'true|false':
                    $nameFilter = array_combine(
                        explode('|', $optionName),
                        [true, false]
                    );

                    foreach ($nameFilter as $name => $filter) {
                        $items = array_keys($option['value'], $filter, true);
                        if ($items) {
                            $cmdPattern[] = "--$name=%s";
                            $cmdArgs[] = escapeshellarg(implode(' ', $items));
                        }
                    }
                    break;

                case 'space-separated':
                    $items = Utils::filterEnabled($option['value']);
                    if ($items) {
                        $cmdPattern[] = "--$optionName=%s";
                        $cmdArgs[] = escapeshellarg(implode(' ', $items));
                    }
                    break;

                case 'as-is':
                    if ($option['value'] instanceof CommandInterface) {
                        $cmd = $option['value']->getCommand();
                    } else {
                        $cmd = (string) $option['value'];
                    }

                    if ($cmd) {
                        $cmdAsIs[] = $cmd;
                    }
                    break;
            }
        }

        $wd = $this->getWorkingDirectory();

        $chDir = $wd ? sprintf('cd %s &&', escapeshellarg($wd)) : '';
        $env = vsprintf(implode(' ', $envPattern), $envArgs);
        $cmd = vsprintf(implode(' ', $cmdPattern), $cmdArgs);
        $asIs = implode(' ', $cmdAsIs);

        return implode(' ', array_filter([$chDir, $env, $cmd, $asIs]));
    }

    protected function getCommandOptions(): array
    {
        return [
            'envVarComposer' => [
                'type' => 'environment',
                'value' => $this->getEnvVarComposer(),
                'name' => 'COMPOSER',
            ],
            'workingDirectory' => [
                'type' => 'other',
                'value' => $this->getWorkingDirectory(),
            ],
            'composerExecutable' => [
                'type' => 'other',
                'value' => $this->getComposerExecutable(),
            ],
        ];
    }
}

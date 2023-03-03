<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Composer\Task;

use Consolidation\AnnotatedCommand\Output\OutputAwareInterface;
use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use Robo\Common\OutputAwareTrait;
use Robo\Contract\CommandInterface;
use Robo\Result;
use Sweetchuck\Robo\Composer\Utils;
use Symfony\Component\Console\Helper\ProcessHelper;
use Symfony\Component\Process\Process;

abstract class CliTaskBase extends TaskBase implements
    CommandInterface,
    ContainerAwareInterface,
    OutputAwareInterface
{

    use ContainerAwareTrait;
    use OutputAwareTrait;

    protected string $envExecutable = '/usr/bin/env';

    protected string $shell = 'bash';

    protected string $command = '';

    protected int $processExitCode = 0;

    protected string $processStdOutput = '';

    protected string $processStdError = '';

    //region Options.

    //region Option - workingDirectory
    public string $workingDirectory = '';

    public function getWorkingDirectory(): string
    {
        return $this->workingDirectory;
    }

    public function setWorkingDirectory(string $workingDirectory): static
    {
        $this->workingDirectory = $workingDirectory;

        return $this;
    }
    // endregion

    //region Option - composerExecutable
    public string $composerExecutable = 'composer';

    public function getComposerExecutable(): string
    {
        return $this->composerExecutable;
    }

    public function setComposerExecutable(string $composerExecutable): static
    {
        $this->composerExecutable = $composerExecutable;

        return $this;
    }
    //endregion

    //region Option - envVarComposer.
    protected ?string $envVarComposer = null;

    public function getEnvVarComposer(): ?string
    {
        return $this->envVarComposer;
    }

    public function setEnvVarComposer(?string $value): static
    {
        $this->envVarComposer = $value;

        return $this;
    }
    //endregion

    // region hideStdOutput
    protected bool $hideStdOutput = true;

    public function getHideStdOutput(): bool
    {
        return $this->hideStdOutput;
    }

    public function setHideStdOutput(bool $hideStdOutput): static
    {
        $this->hideStdOutput = $hideStdOutput;

        return $this;
    }
    // endregion

    //endregion

    public function setOptions(array $options): static
    {
        parent::setOptions($options);

        if (array_key_exists('envVarComposer', $options)) {
            $this->setEnvVarComposer($options['envVarComposer']);
        }

        if (array_key_exists('workingDirectory', $options)) {
            $this->setWorkingDirectory($options['workingDirectory']);
        }

        if (array_key_exists('composerExecutable', $options)) {
            $this->setComposerExecutable($options['composerExecutable']);
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
    public function run()
    {
        $this->command = $this->getCommand();

        return parent::run();
    }

    protected function runHeader(): static
    {
        $this->printTaskInfo($this->command);

        return $this;
    }

    public function runDoIt(): static
    {
        $process = $this
            ->getProcessHelper()
            ->run(
                $this->output(),
                [
                    $this->envExecutable,
                    $this->shell,
                    '-c',
                    $this->command,
                ],
                null,
                $this->getProcessRunCallbackWrapper()
            );

        $this->processExitCode = (int) $process->getExitCode();
        $this->processStdOutput = $process->getOutput();
        $this->processStdError = $process->getErrorOutput();

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
        return $this->processExitCode;
    }

    protected function getTaskResultMessage(): string
    {
        return $this->processStdError;
    }

    protected function getProcessRunCallbackWrapper(): callable
    {
        return function (string $type, string $data): void {
            $this->processRunCallback($type, $data);
        };
    }

    protected function processRunCallback(string $type, string $data): void
    {
        switch ($type) {
            case Process::OUT:
                if (!$this->getHideStdOutput()) {
                    $this->output()->write($data);
                }
                break;

            case Process::ERR:
                $this->printTaskError($data);
                break;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getCommand()
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

    protected function getProcessHelper(): ProcessHelper
    {
        // @todo Check that everything is available.
        return  $this
            ->getContainer()
            ->get('application')
            ->getHelperSet()
            ->get('process');
    }
}

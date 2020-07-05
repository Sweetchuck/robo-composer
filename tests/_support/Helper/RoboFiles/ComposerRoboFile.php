<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Composer\Test\Helper\RoboFiles;

use Consolidation\AnnotatedCommand\CommandResult;
use Consolidation\OutputFormatters\StructuredData\RowsOfFields;
use Robo\Tasks;
use Sweetchuck\Robo\Composer\ComposerTaskLoader;
use Sweetchuck\Robo\Composer\Utils;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Yaml\Yaml;

class ComposerRoboFile extends Tasks
{
    use ComposerTaskLoader;

    /**
     * @command package-paths:basic
     */
    public function packagePathsBasic(string $composerExecutable): int
    {
        $result = $this
            ->taskComposerPackagePaths([
                'workingDirectory' => Utils::getRoboComposerRootDir(),
                'composerExecutable' => $composerExecutable,
            ])
            ->run();

        $stdOutput = $this->output();
        if ($result->wasSuccessful() && isset($result['packagePaths']['sweetchuck/robo-git'])) {
            $stdOutput->writeln('Success');
        } else {
            $stdError = ($stdOutput instanceof ConsoleOutputInterface) ? $stdOutput->getErrorOutput() : $stdOutput;
            $stdError->writeln('Fail');
        }

        return $result->getExitCode();
    }

    /**
     * @command composer:remove-indirect-dependencies
     */
    public function removeIndirectDependencies(
        array $options = [
            'workingDirectory' => '',
            'composerJsonFileName' => '',
        ]
    ): CommandResult {
        $result = $this
            ->taskComposerRemoveIndirectDependencies($options)
            ->run();

        $data = [];

        return CommandResult::dataWithExitCode($data, $result->getExitCode());
    }

    /**
     * @command composer:lock-diff
     *
     * @field-labels
     *   name: Name
     *   version_old: Old
     *   version_new: New
     *   required_as: Required as
     * @default-string-field name
     */
    public function composerLockDiff(
        string $a,
        string $b,
        $options = [
            'format' => 'table',
            'fields' => '',
        ]
    ) {
        $a = $this->processFileName($a);
        $b = $this->processFileName($b);

        $result = $this
            ->taskComposerLockDiffer()
            ->setLockA(json_decode(file_get_contents($a), true))
            ->setLockB(json_decode(file_get_contents($b), true))
            ->run();

        $this
            ->output()
            ->write(Yaml::dump($result['lockDiff'], 99, 4));
    }

    protected function processFileName(string $fileName): string
    {
        return preg_replace('@^/proc/self/fd/(\d+)$@', 'php://fd/$1', $fileName);
    }
}

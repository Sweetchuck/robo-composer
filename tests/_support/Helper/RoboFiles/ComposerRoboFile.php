<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Composer\Test\Helper\RoboFiles;

use Consolidation\AnnotatedCommand\CommandResult;
use Robo\Tasks;
use Sweetchuck\Robo\Composer\ComposerTaskLoader;
use Sweetchuck\Robo\Composer\Utils;
use Symfony\Component\Console\Output\ConsoleOutputInterface;

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
}

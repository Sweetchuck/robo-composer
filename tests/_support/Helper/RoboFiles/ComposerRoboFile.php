<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Composer\Test\Helper\RoboFiles;

use Robo\Tasks;
use Sweetchuck\Robo\Composer\ComposerTaskLoader;
use Sweetchuck\Robo\Composer\Utils;
use Symfony\Component\Console\Output\ConsoleOutputInterface;

class ComposerRoboFile extends Tasks
{
    use ComposerTaskLoader;

    /**
     * @command composer:package-paths
     */
    public function composerPackagePaths(string $composerExecutable): int
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
}

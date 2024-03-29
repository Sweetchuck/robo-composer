<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Composer\Task;

use Robo\Result;
use Robo\Task\BaseTask;
use RuntimeException;
use Sweetchuck\Robo\Composer\Utils;
use Symfony\Component\Filesystem\Path;

class RemoveIndirectDependenciesTask extends BaseTask
{

    protected int $jsonEncodeOptions = \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE;

    // region workingDirectory
    protected string $workingDirectory = '.';

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

    // region composerJsonFileName
    protected string $composerJsonFileName = '';

    public function getComposerJsonFileName(): string
    {
        if ($this->composerJsonFileName !== '') {
            return $this->composerJsonFileName;
        }

        return getenv('COMPOSER') ?: 'composer.json';
    }

    public function setComposerJsonFileName(string $composerJsonFileName): static
    {
        $this->composerJsonFileName = $composerJsonFileName;

        return $this;
    }
    // endregion

    public function setOptions(array $options): static
    {
        if (array_key_exists('workingDirectory', $options)) {
            $this->setWorkingDirectory($options['workingDirectory']);
        }

        if (array_key_exists('composerJsonFileName', $options)) {
            $this->setComposerJsonFileName($options['composerJsonFileName']);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $logger = $this->logger();

        $jsonFileName = Path::join($this->getWorkingDirectory(), $this->getComposerJsonFileName());
        $json = $this->readJsonFile($jsonFileName);
        if ($json === null) {
            $message = "File '$jsonFileName' does not exists";
            $logger->info($message);

            return Result::success($this, $message);
        }

        $lockFileName = Utils::replaceFileExtension($jsonFileName, 'lock');
        $lock = $this->readJsonFile($lockFileName);
        if ($lock === null) {
            $message = "File '$lockFileName' does not exists";
            $logger->info($message);

            return Result::success($this, $message);
        }

        settype($lock['packages'], 'array');
        $this->writeJsonFile($lockFileName, Utils::removeIndirectDependencies($json, $lock));

        return Result::success($this);
    }

    protected function readJsonFile(string $fileName): ?array
    {
        $content = @file_get_contents($fileName);

        return $content === false ? null : json_decode($content, true);
    }

    protected function writeJsonFile(string $fileName, array $data): static
    {
        $result = file_put_contents(
            $fileName,
            json_encode($data, $this->jsonEncodeOptions) . "\n",
        );

        if ($result === false) {
            throw new RuntimeException("Failed to write '$fileName'");
        }

        return $this;
    }
}

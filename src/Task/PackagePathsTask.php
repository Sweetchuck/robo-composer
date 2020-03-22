<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Composer\Task;

use Robo\Contract\CommandInterface;

class PackagePathsTask extends CliTaskBase implements CommandInterface
{
    /**
     * @inheritdoc
     */
    protected $taskName = 'Composer - Package paths';

    /**
     * @inheritdoc
     */
    protected function getAction(): string
    {
        return 'show -P';
    }

    /**
     * @inheritdoc
     */
    protected function runPrepareAssets()
    {
        $this->assets['packagePaths'] = [];

        $stdOutput = trim($this->processStdOutput, "\n\r");
        if (!$stdOutput) {
            return $this;
        }

        $lines = explode("\n", $stdOutput);
        foreach ($lines as $line) {
            $parts = preg_split('/\s+/', $line, 2) + [1 => ''];
            $this->assets['packagePaths'][$parts[0]] = $parts[1];
        }

        return $this;
    }
}

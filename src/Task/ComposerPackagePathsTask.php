<?php

namespace Sweetchuck\Robo\Composer\Task;

use Robo\Contract\CommandInterface;

class ComposerPackagePathsTask extends ComposerTask implements CommandInterface
{
    /**
     * {@inheritdoc}
     */
    protected $taskName = 'Composer - Package paths';

    protected function getAction(): string
    {
        return 'show -P';
    }

    protected function parseOutput()
    {
        $assetNamePrefix = $this->getAssetNamePrefix();
        $this->assets["{$assetNamePrefix}packagePaths"] = [];
        $stdOutput = trim($this->processStdOutput, "\n\r");
        if ($stdOutput) {
            $lines = explode("\n", $stdOutput);
            foreach ($lines as $line) {
                $parts = preg_split('/\s+/', $line, 2) + [1 => ''];
                $this->assets["{$assetNamePrefix}packagePaths"][$parts[0]] = $parts[1];
            }
        }

        return $this;
    }
}

<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Composer\Task;

use Sweetchuck\Robo\Composer\Filter\ArrayFilterByPropertyValue;
use Symfony\Component\Filesystem\Filesystem;

class RemoveIndirectDependenciesTask extends TaskBase
{

    //region Option - workingDirectory
    /**
     * @var string
     */
    public $workingDirectory = '.';

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

    // region Option - fileName
    /**
     * @var string
     */
    protected $fileName = 'composer.json';

    public function getFileName(): string
    {
        return $this->fileName;
    }

    /**
     * @return $this
     */
    public function setFileName(string $fileName)
    {
        $this->fileName = $fileName;

        return $this;
    }
    // endregion

    /**
     * @var Filesystem|null
     */
    protected $fs;

    /**
     * @var int
     */
    protected $jsonEncodeOptions = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;

    public function __construct(?Filesystem $fs = null)
    {
        $this->fs = $fs ?: new Filesystem();
    }

    /**
     * @inheritdoc
     */
    public function setOptions(array $options)
    {
        parent::setOptions($options);

        if (array_key_exists('workingDirectory', $options)) {
            $this->setWorkingDirectory($options['workingDirectory']);
        }

        if (array_key_exists('fileName', $options)) {
            $this->setFileName($options['fileName']);
        }

        return $this;
    }

    public function runDoIt()
    {
        $wd = $this->getWorkingDirectory() ?: '.';
        $jsonFile = "$wd/" . $this->getFileName();
        $lockFile = preg_replace('/\.json$/', '.lock', $jsonFile);
        $jsonContent = file_get_contents($jsonFile);
        $lockContent = file_get_contents($lockFile);
        if ($jsonContent === false || $lockContent === false) {
            return $this;
        }

        $json = json_decode($jsonContent, true);
        $lock = json_decode($lockContent, true);

        $requirements = ($json['require'] ?? []) + ($json['require-dev'] ?? []);
        $filter = new ArrayFilterByPropertyValue();
        $filter->setAllowedValues($requirements);
        $lock['packages'] = array_values(array_filter($lock['packages'], $filter));
        $lock['packages-dev'] = array_values(array_filter($lock['packages-dev'], $filter));

        $this->fs->dumpFile($lockFile, json_encode($lock, $this->jsonEncodeOptions));

        return $this;
    }
}

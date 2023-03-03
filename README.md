
# Robo task wrapper for Composer

[![CircleCI](https://circleci.com/gh/Sweetchuck/robo-composer/tree/3.x.svg?style=svg)](https://circleci.com/gh/Sweetchuck/robo-composer/?branch=3.x)
[![codecov](https://codecov.io/gh/Sweetchuck/robo-composer/branch/3.x/graph/badge.svg?token=859DBVngn4)](https://codecov.io/gh/Sweetchuck/robo-composer/tree/3.x)

@todo


## Install

Run `composer require --dev sweetchuck/robo-composer`


## Task - taskComposerLockDiffer

```php
<?php

declare(strict_types = 1);

class RoboFileExample extends \Robo\Tasks
{
    use \Sweetchuck\Robo\Composer\ComposerTaskLoader;

    /**
     * @hook validate @validateArgumentFileName
     */
    public function validateArgumentFileName(\Consolidation\AnnotatedCommand\CommandData $commandData)
    {
        $argNames = $commandData->annotationData()->getList(__FUNCTION__);
        $input = $commandData->input();
        foreach ($argNames as $argName) {
            assert($input->hasArgument($argName), 'invalid argument name');
            $input->setArgument(
                $argName,
                $this->processFileName($input->getArgument($argName)),
            );
        }
    }

    /**
     * @command composer:lock-diff
     *
     * @validateArgumentFileName fileA,fileB
     */
    public function composerLockDiff(string $fileA, string $fileB)
    {
        $a = json_decode(file_get_contents($fileA), true);
        $b = json_decode(file_get_contents($fileB), true);

        return $this
            ->collectionBuilder()
            ->addTask(
                $this
                    ->taskComposerLockDiffer()
                    ->setLockA($a)
                    ->setLockB($b)
            )
            ->addCode(function (\Robo\State\Data $data): int {
                $this
                    ->output()
                    ->writeln(\Symfony\Component\Yaml\Yaml::dump($data['composer.lockDiff']));

                return 0;
            });
    }

    protected function processFileName(string $fileName): string
    {
        return preg_replace('@^/proc/self/fd/(\d+)$@', 'php://fd/$1', $fileName);
    }
}
```

Run: `vendor/bin/robo composer:lock-diff <(git show 'HEAD^:composer.lock') ./composer.lock`<br />
Example output:
> <pre>symfony/filesystem:
>     name: symfony/filesystem
>     version_old: v4.4.5
>     version_new: v4.4.18
>     required_as: null
> symfony/finder:
>     name: symfony/finder
>     version_old: v4.4.5
>     version_new: v5.2.1
>     required_as: null</pre>


## Task - taskComposerPackagePaths

```php
<?php

declare(strict_types = 1);

class RoboFileExample extends \Robo\Tasks
{
    use \Sweetchuck\Robo\Composer\ComposerTaskLoader;

    /**
     * @command composer:package-paths
     */
    public function composerPackagePaths()
    {
        return $this
            ->collectionBuilder()
            ->addTask($this->taskComposerPackagePaths())
            ->addCode(function (\Robo\State\Data $data): int {
                $output = $this->output();
                foreach ($data['composer.packagePaths'] as $name => $path) {
                    $output->writeln("$name => $path");
                }

                return 0;
            });
    }
}

```

Run: `vendor/bin/robo composer:package-paths`<br />
Example output:
> <pre>symfony/filesystem => /my_project_01/vendor/symfony/filesystem
> symfony/finder => /my_project_01/vendor/symfony/finder</pre>

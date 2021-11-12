<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Composer;

class LockDiffer
{

    protected array $diff = [];

    public function diff(array $a, array $b): array
    {
        $this->diff = [];
        $aPackages = $this->normalizePackages($a);
        $bPackages = $this->normalizePackages($b);

        $packageNames = array_merge(
            array_keys($aPackages),
            array_keys($bPackages)
        );
        sort($packageNames);
        foreach ($packageNames as $name) {
            if (!array_key_exists($name, $aPackages)) {
                $this->addNew($bPackages[$name]);

                continue;
            }

            if (!array_key_exists($name, $bPackages)) {
                $this->addRemoved($aPackages[$name]);

                continue;
            }

            $this->addChanged($aPackages[$name], $bPackages[$name]);
        }

        return $this->diff;
    }

    protected function addNew(array $package)
    {
        $this->diff[$package['name']] = [
            'name' => $package['name'],
            'version_old' => null,
            'version_new' => $package['version'],
            'required_as' => $package['_required-as'],
        ];

        return $this;
    }

    protected function addRemoved(array $package)
    {
        $this->diff[$package['name']] = [
            'name' => $package['name'],
            'version_old' => $package['version'],
            'version_new' => null,
            'required_as' => $package['_required-as'],
        ];

        return $this;
    }

    protected function addChanged(array $a, array $b)
    {
        if (!$this->isChanged($a, $b)) {
            return $this;
        }

        $this->diff[$a['name']] = [
            'name' => $a['name'],
            'version_old' => $a['version'] === $b['version'] ? null : $a['version'],
            'version_new' => $a['version'] === $b['version'] ? null : $b['version'],
            'required_as' => $a['_required-as'] === $b['_required-as'] ?
                null
                : "{$a['_required-as']} to {$b['_required-as']}",
            // @todo Patches.
            // @todo Download URL.
        ];

        return $this;
    }

    protected function isChanged(array $a, array $b): bool
    {
        return $a['version'] !== $b['version'] || $a['_required-as'] !== $b['_required-as'];
    }

    protected function normalizePackages(array $lock): array
    {
        $packages = [];
        foreach (['packages' => 'prod', 'packages-dev' => 'dev'] as $key => $requiredAs) {
            foreach ($lock[$key] ?? [] as $package) {
                $packages[$package['name']] = $package;
                $packages[$package['name']]['_required-as'] = $requiredAs;
            }
        }

        ksort($packages);

        return $packages;
    }
}

<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Composer;

use Sweetchuck\Utils\Filter\ArrayAllowedValueFilter;

class Utils
{

    public static function getRoboComposerRootDir(): string
    {
        return dirname(__DIR__);
    }

    public static function replaceFileExtension(string $fileName, string $newExtension): string
    {
        return preg_replace('/\.[^\.]+$/', ".$newExtension", $fileName);
    }

    /**
     * @param array<string, mixed> $json
     * @param array<string, mixed> $lock
     *
     * @return array<string, mixed>
     */
    public static function removeIndirectDependencies(array $json, array $lock): array
    {
        $keys = [
            'packages',
            'packages-dev',
        ];

        $directDependencies = ($json['require'] ?? []) + ($json['require-dev'] ?: []);
        $filter = new ArrayAllowedValueFilter();
        $filter->setKey('name');
        $filter->setAllowedValues(array_keys($directDependencies));
        foreach ($keys as $key) {
            if (!array_key_exists($key, $lock)) {
                continue;
            }

            $lock[$key] = array_values(array_filter($lock[$key], $filter));
        }

        return $lock;
    }
}

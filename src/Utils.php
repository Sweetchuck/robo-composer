<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Composer;

use Sweetchuck\Utils\Filter\ArrayFilterByPropertyValue;

class Utils
{

    public static function getRoboComposerRootDir(): string
    {
        return dirname(__DIR__);
    }

    /**
     * @deprecated
     */
    public static function filterEnabled(array $items, string $property = 'enabled'): array
    {
        $filtered = [];

        foreach ($items as $key => $value) {
            if ((is_scalar($value) || is_bool($value)) && $value) {
                $filtered[$key] = $value;
            } elseif (is_object($value) && property_exists($value, $property) && $value->$property) {
                // @todo Handle if the $property not exists.
                $filtered[$key] = $value;
            } elseif (is_array($value) && !empty($value[$property])) {
                // @todo Handle if the $property not exists.
                $filtered[$key] = $value;
            }
        }

        return $filtered;
    }

    public static function replaceFileExtension(string $fileName, string $newExtension): string
    {
        return preg_replace('/\.[^\.]+$/', ".$newExtension", $fileName);
    }

    public static function removeIndirectDependencies(array $json, array $lock): array
    {
        $keys = [
            'packages',
            'packages-dev',
        ];

        $directDependencies = ($json['require'] ?? []) + ($json['require-dev'] ?: []);
        $filter = new ArrayFilterByPropertyValue();
        $filter->setProperty('name');
        $filter->setAllowedValues($directDependencies);
        foreach ($keys as $key) {
            if (!array_key_exists($key, $lock)) {
                continue;
            }

            $lock[$key] = array_values(array_filter($lock[$key], $filter));
        }

        return $lock;
    }
}

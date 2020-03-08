<?php

namespace Sweetchuck\Robo\Composer;

class Utils
{

    public static function getRoboComposerRootDir(): string
    {
        return dirname(__DIR__);
    }

    /**
     * @todo Use \Sweetchuck\Utils\Filter\ArrayFilterEnabled.
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
}

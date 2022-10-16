<?php

namespace DataFilter;

/**
 * Utilities for data filter
 */
class Util
{
    public static $FLATTEN_SEPARATOR = '.';

    /**
     * Formats string by replacing ":variable:" with given values
     *
     * @param string $str Input string
     * @param array $args Variables to be replaced
     *
     * @return string
     */
    public static function formatString(string $str, array $args): string
    {
        foreach ($args as $k => $v) {
            $str = preg_replace('/:'. $k. ':/', $v, $str);
        }
        return $str;
    }


    /**
     * Flattens input data
     */
    public static function flatten(array $data, array $flat = [], string $prefix = ''): array
    {
        foreach ($data as $key => $value) {

            // is array -> flatten deeped
            if (is_array($value)) {
                $flat = self::flatten($value, $flat, $prefix. $key. self::$FLATTEN_SEPARATOR);
            }
            // scalar -> use
            else {
                $flat[$prefix. $key] = $value;
            }
        }
        return $flat;
    }

}

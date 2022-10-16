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
     * @param string  $str   Input string
     * @param array   $args  Variables to be replaced
     *
     * @return string
     */
    public static function formatString($str, $args)
    {
        foreach ($args as $k => $v) {
            $str = preg_replace('/:'. $k. ':/', $v, $str);
        }
        return $str;
    }


    /**
     * Flattens input data
     *
     * @param string  $str   Input string
     * @param array   $args  Variables to be replaced
     *
     * @return array
     */
    public static function flatten($data, $flat = [], $prefix = '')
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

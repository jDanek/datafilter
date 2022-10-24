<?php

namespace DataFilter\Predefined;

class Filter
{

    public static function filterTrim(): callable
    {
        return function ($input) {
            return trim($input);
        };
    }

    /**
     * Strips input of HTML tags using the strip_tags() method
     * @param string|null $allowedTags Optional string containing all allowed tags.. see strip_tags()
     */
    public static function filterStripTags(?string $allowedTags = null): callable
    {
        return function ($input) use ($allowedTags) {
            return strip_tags($input, $allowedTags);
        };
    }

    /**
     * Transforms input into a lowercase string
     */
    public static function filterLowercase($encoding = 'utf-8'): callable
    {
        return function ($input) use ($encoding) {
            if (function_exists('mb_strtolower')) {
                return mb_strtolower($input, $encoding);
            }
            return strtolower($input);
        };
    }

    /**
     * Transforms input into a uppercase string
     */
    public static function filterUppercase($encoding = 'utf-8'): callable
    {
        return function ($input) use ($encoding) {
            if (function_exists('mb_strtoupper')) {
                return mb_strtoupper($input, $encoding);
            }
            return strtoupper($input);
        };
    }

    /**
     * Converts the input to a string starting with a capital letter
     */
    public static function filterUcFirst($multibyte = false, $encoding = 'utf-8'): callable
    {
        return function ($input) use ($multibyte, $encoding) {
            if ($multibyte) {
                $firstChar = mb_substr($input, 0, 1, $encoding);
                $then = mb_substr($input, 1, null, $encoding);
                return mb_strtoupper($firstChar, $encoding) . $then;
            }
            return ucfirst($input);
        };
    }
}

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
     * Transforms input into a URL-usable string.. "Bla {blub}" -> "bla-blub"
     */
    public static function filterUrlPart(): callable
    {
        return function ($input) {
            return
                preg_replace('/(?:^\-+|\-+$)/', '',     // tailing|leading "-"
                    preg_replace('/\-\-+/', '-',             // more than one "-"
                        preg_replace('/[^a-z0-9\-_\.~]/u', '-',  // strip not allowed chars
                            strtolower($input))
                    ));
        };
    }

    /**
     * Transforms input into a URL-usable string.. "Bla {blub}" -> "bla-blub" (unicode characters allowed)
     */
    public static function filterUrlPartUnicode(): callable
    {
        return function ($input) {
            return
                preg_replace('/(?:^\-+|\-+$)/', '',       // tailing|leading "-"
                    preg_replace('/\-\-+/', '-',               // more than one "-"
                        preg_replace('/[^\p{L}0-9\-_\.~]/u', '-',  // strip not allowed chars
                            strtolower($input))
                    ));
        };
    }

    /**
     * Transforms input into a lowercase string
     */
    public static function filterLowercase(): callable
    {
        return function ($input) {
            return strtolower($input);
        };
    }

    /**
     * Transforms input into a uppercase string
     */
    public static function filterUppercase(): callable
    {
        return function ($input) {
            return strtoupper($input);
        };
    }

}

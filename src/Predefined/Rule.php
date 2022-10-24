<?php

namespace DataFilter\Predefined;

use Tracy\Debugger;

class Rule
{
    /**
     * @return int|false
     */
    protected static function stringLength($value)
    {
        if (!is_string($value)) {
            return false;
        } elseif (function_exists('mb_strlen')) {
            return mb_strlen($value);
        }
        return strlen($value);
    }

    protected static function isAssociativeArray($input): bool
    {
        // array contains at least one key that's not an can not be cast to an integer
        return count(array_filter(array_keys($input), 'is_string')) > 0;
    }

    /**
     * Validate that a field was "accepted" (based on PHP's string evaluation rules)
     */
    public static function ruleAccepted(): callable
    {
        return function ($input) {
            $acceptable = ['yes', 'y', 'on', 1, '1', true];
            return in_array($input, $acceptable, true);
        };
    }

    /**
     * Validate that a field is an array
     */
    public static function ruleArray(): callable
    {
        return function ($input) {
            return is_array($input);
        };
    }

    /**
     * Validate that a field is numeric
     */
    public static function ruleNumeric(): callable
    {
        return function ($input) {
            return is_numeric($input);
        };
    }

    /**
     * Validate that a field is an integer
     */
    public static function ruleInteger($strict = false): callable
    {
        return function ($input) use ($strict) {
            if ($strict) {
                return preg_match('/^([0-9]|-[1-9]|-?[1-9][0-9]*)$/i', $input);
            }
            return filter_var($input, \FILTER_VALIDATE_INT) !== false;
        };
    }

    /**
     * Validate the length of a string
     */
    public static function ruleLength($requiredLength): callable
    {
        return function ($input) use ($requiredLength) {
            $stringLength = self::stringLength($input);
            return ($stringLength !== false) && $stringLength == $requiredLength;
        };
    }

    /**
     * Validate the length of a string (between)
     */
    public static function ruleLengthBetween($min, $max): callable
    {
        return function ($input) use ($min, $max) {
            $length = self::stringLength($input);
            return ($length !== false)
                && $length >= $min
                && $length <= $max;
        };
    }

    /**
     * Validate the length of a string (min)
     */
    public static function ruleLengthMin($min): callable
    {
        return function ($input) use ($min) {
            $length = self::stringLength($input);
            return ($length !== false) && $length >= $min;
        };
    }

    /**
     * Validate the length of a string (max)
     */
    public static function ruleLengthMax($max): callable
    {
        return function ($input) use ($max) {
            $length = self::stringLength($input);
            return ($length !== false) && $length <= $max;
        };
    }

    /**
     * Validate the size of a field is greater than a minimum value.
     */
    public static function ruleMin($min): callable
    {
        return function ($input) use ($min) {
            if (!is_numeric($input)) {
                return false;
            } elseif (function_exists('bccomp')) {
                return !(bccomp($min, $input, 14) === 1);
            } else {
                return $min <= $input;
            }
        };
    }

    /**
     * Validate the size of a field is less than a maximum value
     */
    public static function ruleMax($max): callable
    {
        return function ($input) use ($max) {
            if (!is_numeric($input)) {
                return false;
            } elseif (function_exists('bccomp')) {
                return !(bccomp($input, $max, 14) === 1);
            } else {
                return $max >= $input;
            }
        };
    }

    /**
     * Validate the size of a field is between min and max values
     */
    public static function ruleBetween($min, $max): callable
    {
        return function ($input) use ($min, $max) {
            if (!is_numeric($input)) {
                return false;
            } else {
                return $min <= $input && $max >= $input;
            }
        };
    }

    /**
     * Validate a field is contained within a list of values
     */
    public static function ruleIn(): callable
    {
        $check = func_get_args();
        return function ($input) use ($check) {
            return in_array($input, $check, true);
        };
    }

    /*
    protected function ruleNotIn($field, $value, $params)
    {
        return !$this->ruleIn($field, $value, $params);
    }
    */

    /**
     * Validate a field contains a given string
     */
    public static function ruleContains($requiredString, $strict = false): callable
    {
        return function ($input) use ($requiredString, $strict) {
            if (empty($requiredString)) {
                return false;
            }
            if (!is_string($requiredString) || !is_string($input)) {
                return false;
            }

            if ($strict) {
                if (function_exists('mb_strpos')) {
                    $isContains = mb_strpos($input, $requiredString) !== false;
                } else {
                    $isContains = strpos($input, $requiredString) !== false;
                }
            } else {
                if (function_exists('mb_stripos')) {
                    $isContains = mb_stripos($input, $requiredString) !== false;
                } else {
                    $isContains = stripos($input, $requiredString) !== false;
                }
            }
            return $isContains;
        };
    }

    /**
     * Validate that all field values contains a given array
     */
    /*
    protected function ruleSubset($field, $value, $params)
    {
        if (!isset($params[0])) {
            return false;
        }
        if (!is_array($params[0])) {
            $params[0] = array($params[0]);
        }
        if (is_scalar($value) || is_null($value)) {
            return $this->ruleIn($field, $value, $params);
        }

        $intersect = array_intersect($value, $params[0]);
        return array_diff($value, $intersect) === array_diff($intersect, $value);
    }
*/

    /**
     * Validate that a field is a valid IP address
     */
    public static function ruleIp()
    {
        return function ($input) {
            return filter_var($input, \FILTER_VALIDATE_IP) !== false;
        };
    }


    /**
     * Validate that a field is a valid IP v4 address
     */
    public static function ruleIpv4(): callable
    {
        return function ($input) {
            return filter_var($input, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV4) !== false;
        };
    }

    /**
     * Validate that a field is a valid IP v6 address
     */
    public static function ruleIpv6(): callable
    {
        return function ($input) {
            return filter_var($input, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV6) !== false;
        };
    }

    /**
     * email - Valid email address
     */
    public static function ruleEmail(): callable
    {
        return function ($input) {
            return filter_var($input, \FILTER_VALIDATE_EMAIL) !== false;
        };
    }

    /**
     * Validate that a field contains only ASCII characters
     */
    public static function ruleAscii(): callable
    {
        return function ($input) {
            // multibyte extension needed
            if (function_exists('mb_detect_encoding')) {
                return mb_detect_encoding($input, 'ASCII', true);
            }
            // fallback with regex
            return 0 === preg_match('/[^\x00-\x7F]/', $input);
        };
    }

    /**
     * Validate that a field contains only alphabetic characters
     */
    public static function ruleAlpha(): callable
    {
        return function ($input) {
            return preg_match('/^([a-z])+$/i', $input);
        };
    }

    /**
     * Validate that a field contains only alpha-numeric characters
     */
    public static function ruleAlphaNum(): callable
    {
        return function ($input) {
            return preg_match('/^([a-z0-9])+$/i', $input);
        };
    }

    /**
     * Validate that a field contains only alpha-numeric characters, dashes, and underscores
     */
    public static function ruleSlug(): callable
    {
        return function ($input) {
            if (is_array($input)) {
                return false;
            }
            return preg_match('/^([-a-z0-9_-])+$/i', $input);
        };
    }

    /**
     * Validate that a field passes a regular expression check
     */
    /*
    public static function ruleRegex($pattern): callable
    {
        return function ($input) use ($pattern) {
            return preg_match($pattern, $input);
        };
    }
    */

    /**
     * Validate that a field is a valid date
     */
    public static function ruleDate(): callable
    {
        return function ($input) {
            if ($input instanceof \DateTime) {
                return true;
            }

            try {
                $date = date_parse($input);
                if ($date['warning_count'] === 0 && $date['error_count'] === 0) {
                    return strlen($date['hour']) === 0;
                } else {
                    return false;
                }
            } catch (\Exception $e) {
                return false;
            }
        };
    }

    /**
     * Validate that a field matches a date format
     */
    public static function ruleDateFormat($format): callable
    {
        return function ($input) use ($format) {
            $parsed = date_parse_from_format($format, $input);
            return $parsed['error_count'] === 0
                && $parsed['warning_count'] === 0;
        };
    }

    /**
     * Validate optional field
     */
    public static function ruleOptional(): callable
    {
        //Always return true
        return function ($input) {
            return true;
        };
    }

    /**
     * Check regex against input
     *
     * @param string $regex Name of the rule (unique per attribute)
     *
     * @return callable
     */
    public static function ruleRegex($regex)
    {
        $args = func_get_args();
        $regex = implode(':', $args);

        /*not in format "/../<modifier>", "#..#<modifier>"  nor "~..~<modifier>" */
        if (!preg_match('/^([\/#~]).+\1[msugex]*$/', $regex)) {
            $regex = '/' . stripslashes($regex) . '/';
        }

        return function ($input) use ($regex) {
            return preg_match($regex, $input);
        };
    }

    /**
     * Check regex against input and returns reveresed result
     *
     * @param string $regex Name of the rule (unique per attribute)
     *
     * @return callable
     */
    public static function ruleRegexInverse($regex)
    {
        $args = func_get_args();
        $regex = implode(':', $args);

        /*not in format "/../<modifier>", "#..#<modifier>"  nor "~..~<modifier>" */
        if (!preg_match('/^([\/#~]).+\1[msugex]*$/', $regex)) {
            $regex = '/' . stripslashes($regex) . '/';
        }

        return function ($input) use ($regex) {
            return !preg_match($regex, $input);
        };
    }

    /**
     * Check whether input is a time string
     *
     * @return callable
     */
    public static function ruleTime()
    {
        return function ($input) {
            $date = null;
            try {
                $date = date_parse("2012-01-01 $input");
                if ($date['warning_count'] === 0 && $date['error_count'] === 0) {
                    return strlen($date['hour']) > 0;
                } else {
                    return false;
                }
            } catch (\Exception $e) {
                return false;
            }
        };
    }

    /**
     * Check whether input is URL compatible
     */
    public static function ruleUrlPart()
    {
        return function ($input) {
            return preg_match('/^(?:[0-9a-z]+[\-_~\.])*[0-9a-z]+$/i', $input);
        };
    }

    /**
     * Check whether input is URL compatible (including unicode letters)
     */
    public static function ruleUrlPartUnicode()
    {
        return function ($input) {
            return preg_match('/^(?:[0-9\p{L}]+[\-_~\.])*[0-9\p{L}]+$/', $input);
        };
    }

    /**
     * Check whether input is a datetime
     *
     * @return callable
     */
    public static function ruleDateTime()
    {
        return function ($input) {
            $date = null;
            try {
                $date = date_parse($input);
                return $date['warning_count'] === 0 && $date['error_count'] === 0;
            } catch (\Exception $e) {
                return false;
            }
        };
    }
}

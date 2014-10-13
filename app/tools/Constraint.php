<?php

namespace App\Tools;

class Constraint
{
    const ARRAY_CONTAINS_ALL_KEYS = 1;
    const ARRAY_CONTAINS_ALL_VALUES = 2;
    const ARRAY_IDENTICAL_TO = 4;

    /**
     * Allows to check if a variable is a string, and if it has the specified min/max length if so
     *
     * @param $strInput
     * @param array $hashOptions : optional min, max values
     *
     * @return boolean
     */
    public static function isString($strInput, array $hashOptions = array())
    {
        $intMin = isset($hashOptions['min']) ? (int) $hashOptions['min'] : -1;
        $intMax = isset($hashOptions['max']) ? (int) $hashOptions['max'] : -1;

        if (!is_string($strInput)) {
            return false;
        }

        if ($intMin !== -1 || $intMax !== -1) {
            return self::validateNumber(
                mb_strlen($strInput),
                'is_integer',
                $intMin !== -1 ? $intMin : null,
                $intMax !== -1 ? $intMax : null
            );
        }
        return true;
    }

    /**
     * Allows to check if a variable is an integer and if it respects an optional range, if so
     *
     * @param $intInput
     * @param array $hashOptions
     *
     * @return boolean
     */
    public static function isInteger($intInput, array $hashOptions = array())
    {
        $intMin = isset($hashOptions['min']) ? (int) $hashOptions['min'] : null;
        $intMax = isset($hashOptions['max']) ? (int) $hashOptions['max'] : null;
        return self::validateNumber($intInput, 'is_integer', $intMin, $intMax);
    }

    /**
     * Allows to check if a variable is a float and if it respects an optional range, if so
     *
     * @param $floatInput
     * @param array $hashOptions
     *
     * @return bool
     */
    public static function isFloat($floatInput, array $hashOptions = array())
    {
        $floatMin = isset($hashOptions['min']) ? (int) $hashOptions['min'] : null;
        $floatMax = isset($hashOptions['max']) ? (int) $hashOptions['max'] : null;
        return self::validateNumber($floatInput, 'is_float', $floatMin, $floatMax);
    }

    /**
     * Internal check function for all numbers' types (int, float)..
     * This function can handle a test for an optional range check.
     *
     * @param $mixedNumber
     * @param $funcCallBack
     * @param $mixedMin
     * @param $mixedMax
     *
     * @return bool
     */
    protected static function validateNumber($mixedNumber, $funcCallBack, $mixedMin, $mixedMax)
    {
        if (!is_null($mixedMin) && !is_null($mixedMax) && $mixedMin > $mixedMax) {
            $mixedTmp = $mixedMax;
            $mixedMax = $mixedMin;
            $mixedMin = $mixedTmp;
        }

        if (call_user_func($funcCallBack, $mixedNumber) === false) {
            return false;
        }
        if (!is_null($mixedMin) && $mixedNumber < $mixedMin) {
            return false;
        }
        if (!is_null($mixedMax) && $mixedNumber > $mixedMax) {
            return false;
        }
        return true;
    }

    /**
     * Check if a variable is a boolean
     * @param $boolInput
     * @return mixed
     */
    public static function isBoolean($boolInput)
    {
        return is_bool($boolInput);
    }

    /**
     * Allows to check if a variable contains a valid email
     *
     * @param $strInput
     * @return bool
     */
    public static function isEmail($strInput)
    {
        return is_string($strInput) && filter_var($strInput, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Allows to determine if an array has keys and/or values,
     * or if it's strictly identical to another array (keys + values)
     *
     * @param array $arrayInput
     * @param array $hashOptions
     *
     * @return boolean
     */
    public static function isArray(array $arrayInput, array $hashOptions = array())
    {
        if (!is_array($arrayInput)) {
            return false;
        }

        if (!isset($hashOptions['flag'])
            ||
            isset($hashOptions['flag']) && !in_array($hashOptions['flag'], array(1, 2, 3, 4, 6, 7), true)) {
            $hashOptions['flag'] = 0;
        }
        if (isset($hashOptions['other'])
            && is_array($hashOptions['other']) && ($hashOptions['flag'] & self::ARRAY_IDENTICAL_TO) !== 0) {
            return empty(array_diff_assoc($arrayInput, $hashOptions['other']));
        } else {
            foreach ($arrayInput as $mixedKey => $mixedValue) {
                if (($hashOptions['flag'] & self::ARRAY_CONTAINS_ALL_KEYS) !== 0) {
                    if (!isset($hashOptions['keys']) || (is_array($hashOptions['keys'])
                            && !in_array($mixedKey, $hashOptions['keys'], true))) {
                        return false;
                    }
                }
                if (($hashOptions['flag'] & self::ARRAY_CONTAINS_ALL_VALUES) !== 0) {
                    if (!isset($hashOptions['values']) || (is_array($hashOptions['values'])
                        && !in_array($mixedValue, $hashOptions['values'], true))) {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    /**
     * Allows to check if a value is null
     * @param $mixedValue
     * @return bool
     */
    public static function isNull($mixedValue)
    {
        return is_null($mixedValue);
    }
}

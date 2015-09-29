<?php
namespace App\Tools;

class DataConstraint
{
    /**
     * @var array
     */
    protected static $hashData;

    /**
     * Factorized code for checking types between several methods
     * @param $strMethodName
     * @param $strFieldName
     * @param array $hashOptions
     * @return bool
     */
    protected static function commonCheck($strMethodName, $strFieldName, array $hashOptions)
    {
        // if required is explicitly facultative
        if (isset($hashOptions['required']) && $hashOptions['required'] === false && !isset(static::$hashData[$strFieldName])) {
            return true;
        }

        // quickfix, ugly, sorry (for string, empty, if required)
        if ($strMethodName === 'isString') {
            $boolRequired = isset($hashOptions['required']) && $hashOptions['required'] === true;
            if ($boolRequired && (!isset(static::$hashData[$strFieldName]) || static::$hashData[$strFieldName] === '')) {
                return false;
            }
        }

        return isset(static::$hashData[$strFieldName])
        && Constraint::$strMethodName(static::$hashData[$strFieldName], $hashOptions);
    }

    /**
     * Binds external data to internal data
     * @param array $hashData
     */
    public static function bindData(array $hashData)
    {
        static::$hashData = $hashData;
    }

    /**
     * Allows to check if an internal variable is a string, and if it has the specified min/max length if so
     *
     * @param string $strFieldName
     * @param array $hashOptions : optional min, max values
     * @return bool|void
     */
    public static function isString($strFieldName, array $hashOptions = array())
    {
        return self::commonCheck('isString', $strFieldName, $hashOptions);
    }

    /**
     * Check if a field contains a integer number
     *
     * @param string $strFieldName
     * @param array $hashOptions
     * @return bool
     */
    public static function isInteger($strFieldName, array $hashOptions = array())
    {
        return self::commonCheck('isInteger', $strFieldName, $hashOptions);
    }

    /**
     * Check if a field contains a float number
     *
     * @param string $strFieldName
     * @param array $hashOptions
     * @return bool
     */
    public static function isFloat($strFieldName, array $hashOptions = array())
    {
        return self::commonCheck('isFloat', $strFieldName, $hashOptions);
    }

    /**
     * Check if a field contains a boolean value
     *
     * @param string $strFieldName
     * @param array $hashOptions
     * @return bool
     */
    public static function isBoolean($strFieldName, array $hashOptions = array())
    {
        return self::commonCheck('isBoolean', $strFieldName, $hashOptions);
    }

    /**
     * Allows to check if an internal variable contains a valid email
     *
     * @param string $strFieldName
     * @param array $hashOptions
     * @return bool
     */
    public static function isEmail($strFieldName, array $hashOptions = array())
    {
        return self::commonCheck('isEmail', $strFieldName, $hashOptions);
    }

    /**
     * Allows to check if an internal variable contains an array which has specific keys/values set
     * or if it is strictly identical to another array
     *
     * @param string $strFieldName
     * @param array $hashOptions
     * @return bool
     */
    public static function isArray($strFieldName, array $hashOptions = array())
    {
        if (!isset(static::$hashData[$strFieldName]) || !is_array(static::$hashData[$strFieldName])) {
            return false;
        }

        return self::commonCheck('isArray', $strFieldName, $hashOptions);
    }

    /**
     * Allows to check if an internal variable is identical to another internal variable
     *
     * @param string $strFieldNameA
     * @param string $strFieldNameB
     * @return boolean
     */
    public static function isIdenticalTo($strFieldNameA, $strFieldNameB)
    {
        return isset(static::$hashData[$strFieldNameA]) && isset(static::$hashData[$strFieldNameB])
        && static::$hashData[$strFieldNameA] === static::$hashData[$strFieldNameB];
    }

    /**
     * Allows to check if an internal variable is null
     * @param string $strFieldName
     * @param array $hashOptions
     * @return boolean
     */
    public static function isNull($strFieldName, array $hashOptions = array())
    {
        if (isset($hashOptions['required']) && $hashOptions['required'] === false && !isset(static::$hashData[$strFieldName])) {
            return true;
        }
        return array_key_exists($strFieldName, static::$hashData) && is_null(static::$hashData[$strFieldName]);
    }
}

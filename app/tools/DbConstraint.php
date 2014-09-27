<?php

namespace App\Tools;

class DbConstraint extends Constraint
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
    protected function commonCheck($strMethodName, $strFieldName, array $hashOptions)
    {
        return isset(static::$hashData[$strFieldName])
        && parent::$strMethodName(static::$hashData[$strFieldName], $hashOptions);
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
     * @param $strFieldName
     * @param array $hashOptions : optionnal min, max values
     * @return bool|void
     */
    public static function isString($strFieldName, array $hashOptions = array())
    {
        if (isset($hashOptions['required']) && $hashOptions['required'] === false && !isset(static::$hashData[$strFieldName])) {
            return true;
        }
        return self::commonCheck('isString', $strFieldName, $hashOptions);
    }

    /**
     * @param $strFieldName
     * @param array $hashOptions
     * @return bool
     */
    public static function isInteger($strFieldName, array $hashOptions = array())
    {
        if (isset($hashOptions['required']) && $hashOptions['required'] === false && !isset(static::$hashData[$strFieldName])) {
            return true;
        }
        return self::commonCheck('isInteger', $strFieldName, $hashOptions);
    }

    /**
     * @param $strFieldName
     * @param array $hashOptions
     * @return bool
     */
    public static function isFloat($strFieldName, array $hashOptions = array())
    {
        if (isset($hashOptions['required']) && $hashOptions['required'] === false && !isset(static::$hashData[$strFieldName])) {
            return true;
        }
        return self::commonCheck('isFloat', $strFieldName, $hashOptions);
    }

    /**
     * @param $strFieldName
     * @param array $hashOptions
     * @return bool
     */
    public static function isBoolean($strFieldName, array $hashOptions = array())
    {
        if (isset($hashOptions['required']) && $hashOptions['required'] === false && !isset(static::$hashData[$strFieldName])) {
            return true;
        }
        return self::commonCheck('isBoolean', $strFieldName, $hashOptions);
    }

    /**
     * Allows to check if an internal variable contains a valid email
     *
     * @param $strFieldName
     * @param array $hashOptions
     * @return bool
     */
    public static function isEmail($strFieldName, array $hashOptions = array())
    {
        if (isset($hashOptions['required']) && $hashOptions['required'] === false && !isset(static::$hashData[$strFieldName])) {
            return true;
        }
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
        if (isset($hashOptions['required']) && $hashOptions['required'] === false && !isset(static::$hashData[$strFieldName])) {
            return true;
        }
        return self::commonCheck('isArray', $strFieldName, $hashOptions);
    }

    /**
     * Allows to check if an internal variable is identical to another internal variable
     *
     * @param $strFieldNameA
     * @param $strFieldNameB
     * @return boolean
     */
    public static function isIdenticalTo($strFieldNameA, $strFieldNameB)
    {
        return isset(static::$hashData[$strFieldNameA]) && isset(static::$hashData[$strFieldNameB])
        && static::$hashData[$strFieldNameA] === static::$hashData[$strFieldNameB];
    }

    /**
     * Allows to check if an internal variable is unique in comparison to a column A
     * inside a table B in a database C
     *
     * @param $strFieldName
     * @param array $hashOptions mapping tables options
     * @return boolean
     */
    public static function isUnique($strFieldName, array $hashOptions = array())
    {
        // TODO
        return false;
    }
}

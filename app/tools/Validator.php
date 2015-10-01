<?php

namespace App\Tools;

class Validator
{
    protected static $arrayMsg = array();
    protected static $boolStatus = true;

    /**
     * Performs a validation test.
     * If the test is faulty, an error message can be specified
     *
     * @param boolean $boolStatus
     * @param string $strMsg Error's message
     * @param string $strFieldName Field's name
     * @param string $strType Error's type
     * @return boolean
     */
    public static function validate($boolStatus, $strMsg = '', $strFieldName = '', $strType = '')
    {
        $boolStatus = (boolean) $boolStatus;
        $strMsg = (string) $strMsg;

        if (!$boolStatus) {
            self::$boolStatus = false;
            if (!empty($strMsg)) {
                if (!empty($strFieldName)) {
                    if (!empty($strType)) {
                        self::$arrayMsg[$strFieldName][$strType] = $strMsg;
                    } else {
                        self::$arrayMsg[$strFieldName][] = $strMsg;
                    }
                } else {
                    self::$arrayMsg[] = $strMsg;
                }
            }
        }
        return $boolStatus;
    }

    /**
     * Indicates the validation flag
     * @return boolean
     */
    public static function isValid()
    {
        return self::$boolStatus;
    }

    /**
     * Returns array of potential errors messages and clear the validator for a potential next use.
     *
     * @param string $strFieldName if specified, tries to return corresponding error messages.
     *  If not empty the validator isn't not cleaned.
     *
     * @return array
     */
    public static function getMessages($strFieldName = '')
    {
        if (array_key_exists($strFieldName, self::$arrayMsg)) {
            return self::$arrayMsg[$strFieldName];
        }

        $arrayMsg = self::$arrayMsg;
        self::reset();
        return $arrayMsg;
    }

    /**
     * Indicates if errors have been collected during the last process
     *
     * @param string $strFieldName if specified, indicates if the corresponding field has error(s)
     * @return boolean
     */
    public static function hasErrors($strFieldName = '')
    {
        if (!empty($strFieldName)) {
            return array_key_exists($strFieldName, self::$arrayMsg) ? !empty(self::$arrayMsg[$strFieldName]) : false;
        }
        return !empty(self::$arrayMsg);
    }

    /**
     * Clear the validator for a potential next use.
     */
    public static function reset()
    {
        self::$arrayMsg = array();
        self::$boolStatus = true;
    }
}

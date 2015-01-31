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
                }
                self::$arrayMsg[] = $strMsg;
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
     * @return array
     */
    public static function getMessages()
    {
        $arrayMsg = self::$arrayMsg;
        self::reset();
        return $arrayMsg;
    }

    /**
     * Clear the validator for a potential next use.
     */
    public static function reset()
    {
        self::$arrayMsg = null;
        self::$boolStatus = true;
    }
}

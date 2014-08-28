<?php

namespace App\Plugins\Tools;

class Validator
{
    protected static $arrayMsg = array();
    protected static $boolStatus = true;

    /**
     * Performs a validation test.
     * If the test is faulty, an error message can be specified
     *
     * @param $boolStatus
     * @param string $strMsg
     * @return bool
     */
    public static function validate($boolStatus, $strMsg = '')
    {
        $boolStatus = (boolean) $boolStatus;
        $strMsg = (string) $strMsg;

        if (!$boolStatus) {
            self::$boolStatus = false;
            if (!empty($strMsg)) {
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

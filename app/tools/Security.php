<?php

namespace App\Tools;

class Security
{
    /**
     * Indicates if an integer shares bit with another integer
     *
     * @param integer $intA
     * @param integer $intB
     * @return bool
     */
    public static function shareANDBits($intA, $intB)
    {
        return ($intA & $intB) !== 0;
    }
}

<?php

namespace App\Tools;

class Tools
{
    /**
     * Replace all non letters or digits by $strReplacementCharacter, trim and lowercase
     * @param string $strInput
     * @param string $strReplacementCharacter
     * @return string
     */
    public static function cleanString($strInput, $strReplacementCharacter = '-')
    {
        return strtolower(trim(preg_replace('/\W+/', $strReplacementCharacter, $strInput), $strReplacementCharacter));
    }

    /**
     * Filter array's keys with values from another one
     * @param array $hashSource
     * @param array $arrayList
     * @param bool $boolIsWL if $arrayList is a white list or not
     * @return array
     */
    public static function filterFromArray(array $hashSource, array $arrayList, $boolIsWL = true)
    {
        $hashResult = array();
        foreach ($hashSource as $strKey => $mixedValue) {
            if ($boolIsWL && in_array($strKey, $arrayList) || !$boolIsWL && !in_array($strKey, $arrayList)) {
                $hashResult[$strKey] = $mixedValue;
            }
        }
        return $hashResult;
    }
}

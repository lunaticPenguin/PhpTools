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

    /**
     * Filter array's keys from an array collection with values from another one
     *
     * @param array $arrayCollection
     * @param array $arrayList
     * @param bool $boolIsWL
     * @return array
     */
    public static function filterCollectionFromArray(array $arrayCollection, array $arrayList, $boolIsWL = true)
    {
        $arrayNewCollection = array();
        foreach ($arrayCollection as $mixedKey => $hashRow) {
            $arrayNewCollection[$mixedKey] = self::filterFromArray($hashRow, $arrayList, $boolIsWL);
        }
        return $arrayNewCollection;
    }
}

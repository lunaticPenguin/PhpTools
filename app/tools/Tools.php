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

    /**
     * Get all current parameters whose the name begins by the specified prefix
     *
     * @param string $strPrefixParam prefix of the searched parameters
     * @param array $hashData data set to search through.
     *
     * @return array
     */
    public static function getAllParamsStartWith($strPrefixParam, array $hashData) {

        $arrayResult = array();
        $intPrefixLength = mb_strlen($strPrefixParam);

        foreach ($hashData as $strKey => $strValue) {
            if (mb_strpos($strKey, $strPrefixParam) !== false) {
                $arrayResult[] = substr($strKey, $intPrefixLength);
            }
        }
        return $arrayResult;
    }
}

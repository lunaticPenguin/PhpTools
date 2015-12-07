<?php

namespace App\Tools;

final class Config
{
    const PATH_TO_CONFIG = '../assets/config';

    public static $hashConfig = array();

    /**
     * Retrieve the configuration key
     * Nested key supported
     *
     * @param string $strKey
     * @param mixed $mixedDefault
     *
     * @return mixed
     */
    public static function get($strKey, $mixedDefault = null)
    {
        $strKey = strtoupper($strKey);
        $arrayPath = explode('.', $strKey);

        if (empty(self::$hashConfig)) {
            self::$hashConfig = include_once self::PATH_TO_CONFIG . '/conf.php';
        }

        return self::getNestedPart(self::$hashConfig, $arrayPath, $mixedDefault);
    }

    /**
     * @param array $hashConfig duplicata of the current config array
     * @param array $arrayPath
     * @param mixed $mixedDefault default value, if specified
     * @return mixed
     *
     */
    private static function getNestedPart(array $hashConfig, array $arrayPath, $mixedDefault = null)
    {
        $strKey = array_shift($arrayPath);
        if (is_null($strKey)) {
            return null;
        }

        if (array_key_exists($strKey, $hashConfig)) {
            if (!empty($arrayPath)) {
                return self::getNestedPart($hashConfig[$strKey], $arrayPath, $mixedDefault);
            } else {
                return $hashConfig[$strKey];
            }
        } else {
            return $mixedDefault;
        }
    }
}

<?php
namespace App\Tools;

/**
 * Translator
 *
 * Class in charged to translate message
 *
 */
class Translator
{
    protected static $arraySupportedLocales = [
        'af-ZA', 'am-ET', 'ar-AE', 'ar-BH', 'ar-DZ', 'ar-EG', 'ar-IQ', 'ar-JO', 'ar-KW', 'ar-LB', 'ar-LY',
        'ar-MA', 'ar-OM', 'ar-QA', 'ar-SA', 'ar-SY', 'ar-TN', 'ar-YE', 'as-IN', 'ba-RU', 'be-BY', 'bg-BG',
        'bn-BD', 'bn-IN', 'bo-CN', 'br-FR', 'ca-ES', 'co-FR', 'cs-CZ', 'cy-GB', 'da-DK', 'de-AT', 'de-CH',
        'de-DE', 'de-LI', 'de-LU', 'dv-MV', 'el-GR', 'en-AU', 'en-BZ', 'en-CA', 'en-GB', 'en-IE', 'en-IN',
        'en-JM', 'en-MY', 'en-NZ', 'en-PH', 'en-SG', 'en-TT', 'en-US', 'en-ZA', 'en-ZW', 'es-AR', 'es-BO',
        'es-CL', 'es-CO', 'es-CR', 'es-DO', 'es-EC', 'es-ES', 'es-GT', 'es-HN', 'es-MX', 'es-NI', 'es-PA',
        'es-PE', 'es-PR', 'es-PY', 'es-SV', 'es-US', 'es-UY', 'es-VE', 'et-EE', 'eu-ES', 'fa-IR', 'fi-FI',
        'fo-FO', 'fr-BE', 'fr-CA', 'fr-CH', 'fr-FR', 'fr-LU', 'fr-MC', 'fy-NL', 'ga-IE', 'gd-GB', 'gl-ES',
        'gu-IN', 'he-IL', 'hi-IN', 'hr-BA', 'hr-HR', 'hu-HU', 'hy-AM', 'id-ID', 'ig-NG', 'ii-CN', 'is-IS',
        'it-CH', 'it-IT', 'ja-JP', 'ka-GE', 'kk-KZ', 'kl-GL', 'km-KH', 'kn-IN', 'ko-KR', 'ky-KG', 'lb-LU',
        'lo-LA', 'lt-LT', 'lv-LV', 'mi-NZ', 'mk-MK', 'ml-IN', 'mn-MN', 'mr-IN', 'ms-BN', 'ms-MY', 'mt-MT',
        'nb-NO', 'ne-NP', 'nl-BE', 'nl-NL', 'nn-NO', 'oc-FR', 'or-IN', 'pa-IN', 'pl-PL', 'ps-AF', 'pt-BR',
        'pt-PT', 'rm-CH', 'ro-RO', 'ru-RU', 'rw-RW', 'sa-IN', 'se-FI', 'se-NO', 'se-SE', 'si-LK', 'sk-SK',
        'sl-SI', 'sq-AL', 'sv-FI', 'sv-SE', 'sw-KE', 'ta-IN', 'te-IN', 'th-TH', 'tk-TM', 'tn-ZA', 'tr-TR',
        'tt-RU', 'ug-CN', 'uk-UA', 'ur-PK', 'vi-VN', 'wo-SN', 'xh-ZA', 'yo-NG', 'zh-CN', 'zh-HK', 'zh-MO',
        'zh-SG', 'zh-TW', 'zu-ZA'
    ];

    protected static $strLocalesPath = '';

    protected static $hashLoadedLocales = [];

    protected static $strLoadedLocale = '';

    public static function forceLocale($strLocaleToForce)
    {
        self::$strLoadedLocale = 'en-US';
        if (in_array($strLocaleToForce, self::$arraySupportedLocales)) {
            self::$strLoadedLocale = $strLocaleToForce;
        }
    }

    public static function retrieveLocale()
    {
        $strLanguage = 'en-US';
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $strLanguage = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 5);
        }

        return $strLanguage;
    }

    public static function setLocalesPath($strPath)
    {
        self::$strLocalesPath = $strPath;
    }

    public static function loadLocale($strForcedLocale = '')
    {
        if ($strForcedLocale !== '') {
            self::forceLocale($strForcedLocale);
        }
        if (empty($strForcedLocale) && self::$strLoadedLocale === '') {
            self::$strLoadedLocale = self::retrieveLocale();
        }

        $strPath = self::$strLocalesPath . '/' . self::$strLoadedLocale . '.php';
        if (!file_exists($strPath)) {
            throw new \Exception('Locale file not found');
        }

        self::loadLocaleFile(self::$strLoadedLocale);
    }

    public static function loadLocaleFile($strLocale)
    {
        if (!in_array($strLocale, self::$arraySupportedLocales)) {
            return false;
        }

        $strPath = self::$strLocalesPath . '/' . $strLocale . '.php';
        if (!file_exists($strPath)) {
            throw new \Exception(sprintf('Locale file not found (%s)', $strPath), 500);
        }
        self::$hashLoadedLocales[self::$strLoadedLocale] = include $strPath;

        return true;
    }

    public static function t($strTextToTranslate, array $hashPattern = [])
    {
        $strText = $strTextToTranslate;
        if (isset(self::$hashLoadedLocales[$strTextToTranslate])) {
            $strText = self::$hashLoadedLocales[$strTextToTranslate];
        }

        foreach ($hashPattern as $strPattern => $mixedReplace) {
            $strText = preg_replace($strPattern, $mixedReplace, $strText);
        }

        return $strText;
    }
}

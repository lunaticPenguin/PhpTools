<?php
namespace App\Tools;

/**
 * Class FileConstraint
 * @package App\Tools
 */
class FileConstraint
{
    const UPLOAD_ERROR_OK = 0;
    const UPLOAD_ERROR_MISSING_FILE = 1;
    const UPLOAD_ERROR_SIZE = 2; // returned when file's size is over the limit
    const UPLOAD_ERROR_MIME = 3; // returned when file's mime type incorrect

    const MT_IMAGES = 0;
    const MT_JPEG   = 1;
    const MT_PNG    = 2;
    const MT_GIF    = 3;
    const MT_BMP    = 4;
    const MT_SVG    = 5;
    const MT_TIFF   = 6;
    const MT_TGA    = 7;
    const MT_ICON   = 8;
    const MT_PDF    = 9;
    const MT_ARCHIVES = 100;
    const MT_ZIP = 101;
    const MT_TAR = 102;
    const MT_TARGZ = 103;

    /*
     * Contains popular  mime types
     */
    protected static $hashMimeTypes = [

        self::MT_JPEG   => ['image/jpeg', 'image/pjpeg'],
        self::MT_PNG    => ['image/png'],
        self::MT_GIF    => ['image/gif'],
        self::MT_BMP    => ['image/bmp'],
        self::MT_SVG    => ['image/svg+xml'],
        self::MT_TIFF   => ['image/tiff'],
        self::MT_TGA    => ['image/x-tga'],
        self::MT_ICON   => ['image/x-icon'],
        self::MT_PDF    => ['application/pdf'],
        self::MT_ZIP    => ['application/zip'], // , 'application/octet-stream'
        self::MT_TAR    => ['application/x-rar-compressed'],
        self::MT_TARGZ  => ['application/gzip', 'application/x-gzip', 'application/x-gtar', 'application/x-tgz']
    ];

    /**
     * Returns predefined list of mime type
     * @param $intType
     * @return array
     */
    public static function getMimeType($intType)
    {
        if (isset(self::$hashMimeTypes[$intType])) {
            return self::$hashMimeTypes[$intType];
        }
        switch ($intType) {
            default:
                return [];
                break;
            case self::MT_IMAGES:
                return self::$hashMimeTypes[self::MT_JPEG] + self::$hashMimeTypes[self::MT_PNG] + self::$hashMimeTypes[self::MT_GIF]
                + self::$hashMimeTypes[self::MT_BMP];
                break;
        }
    }

    /**
     * Perform different checks on files for a specific field (min, max)
     * @param string $strFieldName
     * @param array $hashOptions
     *
     * @return boolean
     */
    public static function generalChecks($strFieldName, array $hashOptions)
    {
        if (!isset($_FILES[$strFieldName])) {
            return false;
        }

        $arrayTemp = $_FILES[$strFieldName]['tmp_name'];
        if (!is_array($arrayTemp)) {
            $arrayTemp = [0 => $_FILES[$strFieldName]['tmp_name']];
        }
        $intCount = 0;
        foreach ($arrayTemp as $strPath) {
            if (file_exists($strPath)) {
                ++$intCount;
            }
        }

        if (isset($hashOptions['min'])) {
            if ($intCount < (int) $hashOptions['min']) {
                return false;
            }
        }
        if (isset($hashOptions['max'])) {
            if ($intCount > (int) $hashOptions['max']) {
                return false;
            }
        }

        return true;
    }

    /**
     * If the size is correct
     * @param string $strFieldName
     * @param integer $intSize unit in bytes
     * @return array format: ['status' => boolean, 'indexes' => []] (only indexes with errors are returned)
     */
    public static function isSizeOK($strFieldName, $intSize)
    {
        $arrayTemp = $_FILES[$strFieldName]['tmp_name'];
        $boolSeveralFiles = true;
        if (!is_array($arrayTemp)) {
            $boolSeveralFiles = false;
            $arrayTemp = [0 => $_FILES[$strFieldName]['tmp_name']];
        }

        $boolStatus = true;
        $hashIndexes = null;
        foreach ($arrayTemp as $intKey => $strPath) {
            if (filesize($strPath) > $intSize) {
                $boolStatus = false;
                $hashIndexes[$intKey] = ($boolSeveralFiles ? $_FILES[$strFieldName]['name'][$intKey] : $_FILES[$strFieldName]['name']);
            }
        }

        return ['status' => $boolStatus, 'indexes' => $hashIndexes, 'unique' => !$boolSeveralFiles];
    }

    /**
     * Check for authorized mime type
     * @param string string $strFieldName
     * @param array $arrayAllowedMimeTypes
     * @return array format: ['status' => boolean, 'indexes' => []] (only indexes with errors are returned)
     */
    public static function isMimeTypeCorrect($strFieldName, array $arrayAllowedMimeTypes)
    {
        $arrayTemp = $_FILES[$strFieldName]['tmp_name'];
        $boolSeveralFiles = true;
        if (!is_array($arrayTemp)) {
            $boolSeveralFiles = false;
            $arrayTemp = [0 => $_FILES[$strFieldName]['tmp_name']];
        }

        $boolStatus = true;
        $hashIndexes = [];
        foreach ($arrayTemp as $intKey => $strPath) {
            if (file_exists($strPath) && !in_array(mime_content_type($strPath), $arrayAllowedMimeTypes)) {
                $boolStatus = false;
                $hashIndexes[$intKey] = ($boolSeveralFiles ? $_FILES[$strFieldName]['name'][$intKey] : $_FILES[$strFieldName]['name']);
            }
        }

        return ['status' => $boolStatus, 'indexes' => $hashIndexes, 'unique' => !$boolSeveralFiles];
    }

    /**
     * Upload a file
     * @param $strOriginPath
     * @param $strDestinationPath
     * @return bool
     */
    public static function uploadFile($strOriginPath, $strDestinationPath)
    {
        if (!(file_exists($strOriginPath) || !file_exists($strDestinationPath))) {
            return false;
        }

        return move_uploaded_file($strOriginPath, $strDestinationPath);
    }
}

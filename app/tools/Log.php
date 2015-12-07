<?php

namespace App\Tools;

/**
 * Class Log.
 * Allows to store logs on different supports.
 * @package App\Tools
 */
class Log
{
    const LVL_INFO      = 1;
    const LVL_DEBUG     = 2;
    const LVL_WARNING   = 4;
    const LVL_ERROR     = 8;

    /**
     * Proceed to log
     * @param string $strMsg
     * @param int $intLevel
     */
    public static function log($strMsg, $intLevel = self::LVL_DEBUG)
    {
        $hashLogLevels = Config::get('LOG', array('file' => 0, 'db' => 0));
        foreach ($hashLogLevels as $strSupport => $intConfiguredLevel) {
            if (($intLevel & $intConfiguredLevel) !== 0) {

                if (is_array($strMsg) || is_object($strMsg)) {
                    $strMsg = print_r((array) $strMsg, true);
                }

                switch ($strSupport) {
                    default:
                    case 'file':
                        self::logFile($strMsg, $intLevel);
                        break;
                    case 'db':
                        self::logDb($strMsg, $intLevel);
                }
            }
        }
    }

    /**
     * Save message into the server's log file
     * @param string $strMsg
     * @param integer $intLevel
     */
    protected static function logFile($strMsg, $intLevel)
    {
        $strPrefix = (new \DateTime())->format('Y-m-d H:i:s ');
        switch ($intLevel) {
            case self::LVL_INFO:
                $strPrefix .= '[INFO] - ';
                break;
            case self::LVL_DEBUG:
                $strPrefix .= '[DEBUG] - ';
                break;
            case self::LVL_WARNING:
                $strPrefix .= '[WARN] - ';
                break;
            default:
                $strPrefix .= '[ERROR] - ';
                break;
        }

        error_log($strPrefix . $strMsg);
    }


    /**
     * Save message into database(s)
     * @param string $strMsg
     * @param integer $intLevel
     */
    protected static function logDb($strMsg, $intLevel)
    {
        // todo
    }
}

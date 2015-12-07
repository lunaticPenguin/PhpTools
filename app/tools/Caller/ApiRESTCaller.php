<?php

namespace App\Tools\Caller;

abstract class ApiRESTCaller
{
    const STATUS_SUCCESS        = 0; // everything is fine
    const STATUS_ERROR_CURL     = 1; // error due to cURL
    const STATUS_ERROR_FORMAT   = 2; // error due to returned unreadable data

    /**
     * Get common information for different curl calls
     *
     * @param string $strUri
     * @param string $strHttpType
     * @param array $hashHttpParameters
     * @param array $hashParameters
     *
     * @return mixed data returned from ApiCaller::handleExecution()
     */
    protected static function exec($strUri, $strHttpType, $hashHttpParameters, $hashParameters)
    {
        $resCurl = curl_init();

        $hashCurlOptions = array(
            CURLOPT_CUSTOMREQUEST       => $strHttpType,
            CURLOPT_RETURNTRANSFER      => true, // returns data instead of printing it
            CURLOPT_CONNECTTIMEOUT      => 50, // timeout 5sec
            CURLOPT_HTTPHEADER          => array(
                'Content-Type: application/json; charset=utf-8'
            )
        );

        $hashCurlOptions += $hashHttpParameters;

        if (in_array(strtolower($strHttpType), array('post', 'put'), true)) {
            $strData = json_encode($hashParameters);
//            var_dump($strData, strlen($strData));exit;
            //$hashCurlOptions[CURLOPT_HTTPHEADER][] = 'Accept: */*';
            $hashCurlOptions[CURLOPT_POSTFIELDS] = $strData;
        } else {
            if (!empty($hashParameters)) {
                $strUri .= '?' . http_build_query($hashParameters);
            }
        }

        $hashCurlOptions[CURLOPT_URL] = $strUri;
        curl_setopt_array($resCurl, $hashCurlOptions);

        //var_dump($strUri, $hashCurlOptions, $strData);exit;

        return static::handleExecution($resCurl);
    }

    /**
     * Handles cURL call.
     * Must be overridden to allow another behaviour.
     *
     * @param resource $resCurl cURL instance
     *
     * @return array
     */
    protected static function handleExecution($resCurl)
    {
        $strData = curl_exec($resCurl);

        $intError = curl_errno($resCurl);
        if ($intError !== CURLE_OK) {
            $hashFail = array(
                'status'    => static::STATUS_ERROR_CURL,
                'data'      => null,
                'message'   => sprintf('cURL error (%d) - %s', $intError, curl_error($resCurl))
            );
            curl_close($resCurl);
            return $hashFail;
        }
        curl_close($resCurl);

        $hashData = json_decode($strData, true);

        $intError = json_last_error();
        if ($intError !== JSON_ERROR_NONE) {
            return array(
                'status'    => static::STATUS_ERROR_FORMAT,
                'data'      => $strData,
                'message'   => sprintf('json_decode error (%d) - %s', $intError, json_last_error_msg())
            );
        }

        return array(
            'status'    => static::STATUS_SUCCESS,
            'data'      => $hashData,
            'message'   => ''
        );
    }

    /**
     * @param string $strUri
     * @param array $hashParameters
     * @param array $hashHttpParameters
     *
     * @return array
     */
    public static function get($strUri, array $hashParameters, array $hashHttpParameters = array())
    {
        return static::exec($strUri, 'GET', $hashHttpParameters, $hashParameters);
    }

    /**
     * @param string $strUri
     * @param array $hashParameters
     * @param array $hashHttpParameters
     *
     * @return array
     */
    public static function put($strUri, array $hashParameters, array $hashHttpParameters = array())
    {
        return static::exec($strUri, 'PUT', $hashHttpParameters, $hashParameters);
    }

    /**
     * @param string $strUri
     * @param array $hashParameters
     * @param array $hashHttpParameters
     *
     * @return array
     */
    public static function post($strUri, array $hashParameters, array $hashHttpParameters = array())
    {
        return static::exec($strUri, 'POST', $hashHttpParameters, $hashParameters);
    }

    /**
     * @param string $strUri
     * @param array $hashParameters
     * @param array $hashHttpParameters
     *
     * @return array
     */
    public static function delete($strUri, array $hashParameters, array $hashHttpParameters = array())
    {
        return static::exec($strUri, 'DELETE', $hashHttpParameters, $hashParameters);
    }
}

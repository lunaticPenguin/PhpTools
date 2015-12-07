<?php

namespace App\Models;

use App\Exceptions\ModelException;
use App\Observers\ObserverHandler;

abstract class AbstractNoSqlModel extends AbstractModel
{
    const PARAM_NULL = 0;
    const PARAM_INT = 1;
    const PARAM_FLOAT = 4;
    const PARAM_STR = 2;
    const PARAM_EMAIL = 3;
    const PARAM_BOOL = 5;
    const PARAM_ARRAY = 6;
    const PARAM_MIXED = 7; // undefined type

    /**
     * Describes informations required to communicate correctly with the server
     * @var array
     */
    protected static $hashInfos = array(
        'database'  => DATABASE_NAME,
        'document'  => '',
        'fields'    => array(
            // automatic fields are _id, _rev, _type
        )
    );

    /**
     * The http query used to communicate with the no-sql server
     * @var string
     */
    protected static $strHttpQuery = '';

    /**
     * Returns information about the current model
     * @param string $strInformation database|alias|primary_key|columns|available_columns
     * @return mixed
     */
    public static function getModelInformation($strInformation = 'document')
    {
        if (!in_array($strInformation, array_keys(static::$hashInfos))) {
            $strInformation = 'document';
        }
        return static::$hashInfos[$strInformation];
    }

    /**
     * @inheritDoc
     */
    public static function createSafely(array $hashData)
    {
        $strId = '';
        try {
            $strId = static::create($hashData);
        } catch (ModelException $e) {
            ObserverHandler::applyHook(
                'model_create_failure',
                array(
                    'model'     => get_called_class(),
                    'data'      => $hashData,
                    'exception' => $e,
                    'query'     => static::$strHttpQuery
                )
            );
        }
        return $strId;
    }

    /**
     * @inheritDoc
     */
    public static function updateSafely(array $hashData, array $arrayColumnList = array())
    {
        $strId = '';
        try {
            $strId = static::updateById($hashData);
        } catch (ModelException $e) {
            ObserverHandler::applyHook(
                'model_update_failure',
                array(
                    'model'     => get_called_class(),
                    'data'      => $hashData,
                    'exception' => $e,
                    'query'     => static::$strHttpQuery
                )
            );
        }
        return $strId;
    }

    /**
     * @inheritDoc
     */
    public static function deleteSafely(array $arrayInputIds)
    {
        $intCountRows = 0;
        try {
            $intCountRows = static::deleteByListId($arrayInputIds);
        } catch (ModelException $e) {
            ObserverHandler::applyHook(
                'model_delete_failure',
                array(
                    'model'     => get_called_class(),
                    'data'      => $arrayInputIds,
                    'exception' => $e,
                    'query'     => static::$strHttpQuery
                )
            );
        }
        return $intCountRows;
    }

    /**
     * @inheritDoc
     */
    public static function executeSafely($strMethodName, $mixedParams)
    {$mixedResult = null;
        try {
            $mixedResult = static::$strMethodName($mixedParams);
        } catch (ModelException $e) {
            ObserverHandler::applyHook(
                'model_custom_query_failure',
                array(
                    'model'     => get_called_class(),
                    'method'    => $strMethodName,
                    'data'      => $mixedParams,
                    'exception' => $e,
                    'query'     => static::$strHttpQuery
                )
            );
        }
        return $mixedResult;
    }
}

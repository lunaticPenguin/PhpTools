<?php

namespace App\Models;

use App\Tools\Config;
use App\Exceptions\ModelException;
use App\Observers\ObserverHandler;
use App\Tools\Caller\ApiRESTCaller;
use App\Tools\Constraint;
use App\Tools\Validator;

abstract class AbstractCouchDbModel extends AbstractNoSqlModel
{
    /**
     * Create initial base for an http query to the database server
     *
     * @param string $strDocumentName document's name
     * @return string
     */
    protected static function getHttpQueryBase($strDocumentName = '')
    {
        return sprintf(
            '%s://%s:%d/%s',
            Config::get('DATABASE.COUCHDB.IS_HTTPS') ? 'https' : 'http',
            Config::get('DATABASE.COUCHDB.HOST', ''),
            Config::get('DATABASE.COUCHDB.PORT', ''),
            static::getModelInformation('database')
        );
    }


    /**
     * Allows to indicate which column needs to have specific type with several options
     * before any insert or update query attempts.
     * This method MUST BE overridden.
     *
     * @param array $hashData
     * @param bool $boolIsUpdating
     * @param array $hashOptions
     *
     * @return boolean
     */
    protected static function validateData(array &$hashData, $boolIsUpdating, array $hashOptions = [])
    {
        $strCurrentDatetime = (new \DateTime())->format('Y-m-d H:i:s');

        if (!$boolIsUpdating) {
            if (isset($hashData['updated_at'])) {
                unset($hashData['updated_at']);
            }
            if (array_key_exists('created_at', static::$hashInfos['fields'])) {
                $hashData['updated_at'] = $strCurrentDatetime;
            }
        } else {
            if (isset($hashData['created_at'])) {
                unset($hashData['created_at']);
            }
            if (array_key_exists('updated_at', static::$hashInfos['fields'])) {
                $hashData['updated_at'] = $strCurrentDatetime;
            }
        }

        $hashData['document_type'] = static::getModelInformation('document');
        if ($boolIsUpdating && !isset($hashData['_rev'])) {
            Validator::validate(false, sprintf('Missing "_rev" field in order to update'));
        }

        self::checkFields(static::getModelInformation('fields'), $hashData);

        if (!$boolIsUpdating) {
            $hashData['_id'] = sprintf('%s_%s', $hashData['document_type'], sha1(uniqid()));
        }

        return Validator::isValid();
    }

    /**
     * Validates document fields recursively
     *
     * @param array $hashFields
     * @param array $hashData
     */
    protected static function checkFields(array $hashFields, array $hashData)
    {
        foreach ($hashFields as $strFieldName => $hashFieldInfos) {

            if (!isset($hashData[$strFieldName])) {
                Validator::validate(false, sprintf("Missing '%s' field in order to save record", $strFieldName));
            }

            $intDataType = isset($hashFieldInfos['type']) ? $hashFieldInfos['type'] : self::PARAM_MIXED;
            $mixedValue = isset($hashData[$strFieldName]) ? $hashData[$strFieldName] : null;

            switch ($intDataType) {

                case self::PARAM_ARRAY:

                    $hashConditions = isset($hashFieldInfos['conditions']) ? $hashFieldInfos['conditions'] : array();
                    $intSubDataType = isset($hashConditions['type']) ? $hashConditions['type'] : null;
                    $hashSubData = isset($hashData[$strFieldName]) ? $hashData[$strFieldName] : array();

                    //$hashConditions
                    switch ($intSubDataType) {
                        case self::PARAM_ARRAY:
                        case self::PARAM_MIXED:
                            if (empty($hashSubData)) {
                                Validator::validate(
                                    false,
                                    sprintf(
                                        '%s (type: %s) has no data',
                                        $strFieldName,
                                        $intSubDataType === self::PARAM_ARRAY
                                            ? 'AbstractNoSqlModel::PARAM_ARRAY'
                                            : 'AbstractNoSqlModel::PARAM_MIXED'
                                    )
                                );
                                return;
                            }
                            self::checkFields($hashFieldInfos['data'], $hashSubData);
                            break;
                        default: // all others datatypes
                            self::checkFields($hashFieldInfos['data'], $hashSubData);
                            break;
                    }
                    break;
                case self::PARAM_INT:
                    Validator::validate(
                        Constraint::isInteger($mixedValue, $hashFieldInfos),
                        sprintf(
                            "%s (%s) isn't a valid integer with following conditions : (%s)",
                            $strFieldName,
                            (string) is_null($mixedValue) ? 'null' : $mixedValue,
                            json_encode($hashFieldInfos, true)
                        )
                    );
                    break;
                case self::PARAM_FLOAT:
                    Validator::validate(
                        Constraint::isFloat($mixedValue, $hashFieldInfos),
                        sprintf(
                            "%s %s isn't a valid float with following conditions : (%s)",
                            $strFieldName,
                            (string) is_null($mixedValue) ? 'null' : $mixedValue,
                            json_encode($hashFieldInfos, true)
                        )
                    );
                    break;
                case self::PARAM_STR:
                    Validator::validate(
                        Constraint::isString($mixedValue, $hashFieldInfos),
                        sprintf(
                            "%s (%s) isn't a valid string with following conditions : (%s)",
                            $strFieldName,
                            (string) is_null($mixedValue) ? 'null' : $mixedValue,
                            json_encode($hashFieldInfos, true)
                        )
                    );

                    if (isset($hashFieldInfos['email']) && $hashFieldInfos['email'] === true) {
                        Validator::validate(
                            Constraint::isEmail($mixedValue),
                            sprintf(
                                "%s (%s) isn't a valid email",
                                $strFieldName,
                                (string) is_null($mixedValue) ? 'null' : $mixedValue
                            )
                        );
                    }
                    break;
                case self::PARAM_BOOL:
                    Validator::validate(
                        Constraint::isBoolean($mixedValue),
                        sprintf(
                            "%s (%s) isn't a boolean value",
                            $strFieldName,
                            (string) is_null($mixedValue) ? 'null' : $mixedValue
                        )
                    );
                    break;
                case self::PARAM_NULL:
                    Validator::validate(
                        Constraint::isNull($mixedValue),
                        sprintf(
                            "%s (%s) isn't null",
                            $strFieldName,
                            (string) is_null($mixedValue) ? 'null' : $mixedValue
                        )
                    );
                    break;
            }
        }
    }

    /**
     * Complete missing fields on document after a schema update.
     * It adds default value corresponding to the field type.
     *
     * @param array $hashSchemaFields schema fields (can be fields' part if there are sub-array(s) in schema)
     * @param array $hashData data to complete (potentially)
     * @return array
     */
    protected static function completeMissingFields(array $hashSchemaFields, array $hashData)
    {
        // TODO
        foreach ($hashSchemaFields as $strFieldName => $hashFieldInfos) {
            if (!isset($hashData[$strFieldName])) {
                $intDataType = isset($hashFieldInfos['type']) ? $hashFieldInfos['type'] : self::PARAM_NULL;
                switch ($intDataType) {
                    case self::PARAM_NULL:
                        $hashData[$strFieldName] = null;
                        break;
                    case self::PARAM_STR:
                        $hashData[$strFieldName] = '';
                        break;
                }
            }
        }
        return array();
    }

    /**
     * @inheritDoc
     */
    public static function create(array $hashData)
    {
        $strUrl = static::getHttpQueryBase(static::getModelInformation('document'));

        Validator::reset();
        if (!static::validateData($hashData, false)) {
            throw new ModelException(
                sprintf(
                    '%s::create() - Invalid input data (%s)',
                    get_called_class(),
                    implode(', ', Validator::getMessages())
                )
            );
        }

        $strUrl .= '/' . $hashData['_id'];

        $hashReturn = static::checkAPIResult(ApiRESTCaller::put($strUrl, $hashData));

        if ($hashReturn['status'] === ApiRESTCaller::STATUS_SUCCESS) {
            if (is_array($hashReturn['data']) && isset($hashReturn['data']['id'])) {
                return $hashReturn['data']['id'];
            }
        }
        return '';
    }

    /**
     * @inheritDoc
     */
    public static function updateById(array $hashData, array $arrayColumnList = [], array $hashOptions = [])
    {
        if (!isset($hashData['_id'])) {
            throw new ModelException(
                sprintf(
                    '%s::getById() - Missing _id parameter',
                    get_called_class()
                )
            );
        }

        $hashDocumentData = static::getById($hashData['_id']);
        $strUrl = sprintf(
            '%s/%s',
            static::getHttpQueryBase(static::getModelInformation('document')),
            $hashData['_id']
        );

        $hashData += $hashDocumentData;

//        $hashData = self::completeMissingFields(self::getModelInformation('fields'), $hashData);

        Validator::reset();
        if (!static::validateData($hashData, false, $hashOptions)) {
            throw new ModelException(
                sprintf(
                    '%s::updateById() - Invalid input data (%s)',
                    get_called_class(),
                    implode(', ', Validator::getMessages())
                )
            );
        }

        $hashReturn = static::checkAPIResult(ApiRESTCaller::put($strUrl, $hashData));
        return $hashReturn['status'] === ApiRESTCaller::STATUS_SUCCESS ? 1 : 0;
    }

    /**
     * @inheritDoc
     */
    public static function deleteByListId(array $arrayInputIds)
    {
        throw new ModelException(sprintf('Unimplemented method (%s::%s())', get_called_class(), __FUNCTION__));
    }

    /**
     * @inheritDoc
     */
    public static function deleteById($intId)
    {
        // TODO: Implement deleteById() method.
    }

    /**
     * @inheritDoc
     */
    public static function getById($mixedId, array $arrayColumns = array())
    {
        if (empty($mixedId)) {
            throw new ModelException(
                sprintf(
                    '%s::getById() - Missing _id parameter',
                    get_called_class()
                )
            );
        }

        $strUrl = sprintf(
            '%s/%s',
            static::getHttpQueryBase(static::getModelInformation('document')),
            $mixedId
        );

        $hashReturn = static::checkAPIResult(ApiRESTCaller::get($strUrl, array()));
        return $hashReturn['status'] === ApiRESTCaller::STATUS_SUCCESS ? $hashReturn['data'] : array();
    }

    /**
     * @inheritDoc
     */
    public static function getByListId(array $arrayInputIds, array $arrayColumns = array(), array $hashOptions = array())
    {
        throw new ModelException(sprintf('Unimplemented method (%s::%s())', get_called_class(), __FUNCTION__));
    }

    /**
     * @inheritDoc
     */
    public static function getGenericList(array $arrayColumns, array $hashOptions = array())
    {
        throw new ModelException(sprintf('Unimplemented method (%s::%s())', get_called_class(), __FUNCTION__));
    }

    /**
     * Format API result
     *
     * @param array $hashAPIResult
     * @return array formatted data
     */
    protected static function checkAPIResult(array $hashAPIResult)
    {
        if (!isset($hashAPIResult['status']) || $hashAPIResult['status'] !== ApiRESTCaller::STATUS_SUCCESS) {
            ObserverHandler::applyHook('couchdb_communication_error', $hashAPIResult);
        }
        return $hashAPIResult;
    }
}

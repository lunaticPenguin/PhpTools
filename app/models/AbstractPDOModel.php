<?php

namespace App\Models;

use App\Exceptions\ModelException;
use App\Exceptions\ModelQueryException;
use App\Observers\ObserverHandler;
use App\Tools\Constraint;
use App\Tools\Validator;

/**
 * Class AbstractPDOModel.
 * Factorize all basic methods for children models and provide them CRUD operations.
 *
 * Some events are dispatched through the model use:
 *  - model_create_XXXX_YYYY
 *  - model_update_by_id_XXXX_YYYY
 *  - delete_row_by_ids_XXXX_YYYY
 *
 * with XXXX as database name and YYYY as the name of the table concerned by the query.
 *
 * If use of [create|update|delete|execute]Safely, some events are also dispatched on failure (plus the events listed below):
 *
 * - pdo_model_create_failure
 * - pdo_model_update_failure
 * - pdo_model_delete_failure
 * - pdo_model_custom_query_failure
 *
 * @package App\Models
 */
abstract class AbstractPDOModel extends AbstractModel implements ITransactionalModel
{
    protected static $hashInfos = array(
        'database'      => '',
        'table'         => '',
        'alias'         => '',
        'primary_key'   => '',
        'columns'       => array()
    );

    /**
     * PDO instance.
     * (A better way is to use the DI pattern, but here the library can be used in many projects)
     * @var \PDO instance
     */
    protected static $objDb;

    /**
     * Inject PDO instance to the current model
     * @param \PDO $objDb
     */
    public static function setPdoInstance($objDb)
    {
        static::$objDb = $objDb;
    }

    /**
     * Allows to indicate which column needs to have specific type with several options
     * before any insert or update query attempts.
     * This method MUST be overridden or called to keep coherent models.
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
        $strCreatedAtFieldName = sprintf('%s_created_at', static::$hashInfos['alias']);
        $strUpdatedAtFieldName = sprintf('%s_updated_at', static::$hashInfos['alias']);

        if (!$boolIsUpdating) {
            if (isset($hashData[$strUpdatedAtFieldName])) {
                unset($hashData[$strUpdatedAtFieldName]);
            }
            if (array_key_exists($strCreatedAtFieldName, static::$hashInfos['columns'])) {
                $hashData[$strCreatedAtFieldName] = $strCurrentDatetime;
            }
        } else {
            if (isset($hashData[$strCreatedAtFieldName])) {
                unset($hashData[$strCreatedAtFieldName]);
            }
            if (!isset($hashOptions['ignore_updated_at']) || $hashOptions['ignore_updated_at'] !== true) {
                if (array_key_exists($strUpdatedAtFieldName, static::$hashInfos['columns'])) {
                    $hashData[$strUpdatedAtFieldName] = $strCurrentDatetime;
                }
            }
        }

        return true;
    }

    /**
     * Allows to create a row in the suitable table
     *
     * @param array $hashData
     * @return int
     * @throws ModelException|\PDOException
     */
    public static function create(array $hashData)
    {
        Validator::reset();
        if (!static::validateData($hashData, false)) {
            throw new ModelException(sprintf('CREATE - Invalid input data for %s', get_called_class()));
        }

        $hashKeys = array();
        $hashValues = array();
        foreach (static::$hashInfos['columns'] as $strColumn => $hashColumnInfo) {
            if (static::$hashInfos['primary_key'] !== $strColumn) {
                $hashKeys[$strColumn] = ':' .  $strColumn;
                $hashValues[$strColumn] = (isset($hashData[$strColumn]) ? $hashData[$strColumn] : null);
            }
        }
        $strSql = sprintf(
            "INSERT INTO %s.%s (%s) VALUES (%s)",
            static::$hashInfos['database'],
            static::$hashInfos['table'],
            implode(',', array_keys($hashKeys)),
            implode(',', $hashKeys)
        );

        $objStatement = static::$objDb->prepare($strSql);

        // values bind
        foreach ($hashValues as $strColumn => $mixedValue) {
            $intType = isset(static::$hashInfos['columns'][$strColumn]['type'])
                ? static::$hashInfos['columns'][$strColumn]['type']
                : \PDO::PARAM_STR;
            $objStatement->bindValue(':' .  $strColumn, $mixedValue, $intType);
        }

        $boolStatus = $objStatement->execute();
        $intId = $boolStatus ? (int) static::$objDb->lastInsertId() : 0;

        // event
        $strHook = sprintf('pdo_model_create_%s_%s', static::$hashInfos['database'], static::$hashInfos['table']);
        ObserverHandler::applyHook($strHook, ['data' => $hashValues, 'success' => $boolStatus, 'id' => $intId]);

        return $intId;
    }

    /**
     * Allows to update a row whose an identifier is provided
     * @param array $hashData
     * @param array $arrayColumnList columns' list (optional)
     * @param array $hashOptions
     * @return integer number of affected rows
     * @throws ModelException|\PDOException
     */
    public static function updateById(array $hashData, array $arrayColumnList = [], array $hashOptions = [])
    {
        Validator::reset();
        if (!static::validateData($hashData, true, $hashOptions)) {
            throw new ModelException(sprintf('UPDATE - Invalid input data for %s', get_called_class()));
        }

        $hashSqlParts = array();
        $hashValues = array();
        foreach ($hashData as $strColumn => $mixedValue) {
            if (isset(static::$hashInfos['columns'][$strColumn])) {
                if (!empty($arrayColumnList)) {
                    if (in_array($strColumn, $arrayColumnList)) {
                        $hashSqlParts[$strColumn] = $strColumn . '=:' . $strColumn;
                    }
                } else {
                    $hashSqlParts[$strColumn] = $strColumn . '=:' . $strColumn;
                }
                $hashValues[$strColumn] = $mixedValue;
            }
        }
        $strSql = sprintf(
            "UPDATE %s.%s SET %s WHERE %s=:%s_id",
            static::$hashInfos['database'],
            static::$hashInfos['table'],
            implode(',', $hashSqlParts),
            static::$hashInfos['primary_key'],
            static::$hashInfos['alias']
        );

        $objStatement = static::$objDb->prepare($strSql);

        // values bind
        foreach ($hashValues as $strColumn => $mixedValue) {
            $intType = isset(static::$hashInfos['columns'][$strColumn]['type'])
                ? static::$hashInfos['columns'][$strColumn]['type']
                : \PDO::PARAM_STR;
            $objStatement->bindValue(':' .  $strColumn, $mixedValue, $intType);
        }

        $boolStatus = $objStatement->execute();
        $intRowCount = $objStatement->rowCount();

        // event
        $strHook = sprintf('pdo_model_update_by_id_%s_%s', static::$hashInfos['database'], static::$hashInfos['table']);
        ObserverHandler::applyHook($strHook, ['data' => $hashValues, 'success' => $boolStatus, 'count' => $intRowCount]);

        return $intRowCount;
    }

    /**
     * Allow to delete several using an identifiers list
     * @param array $arrayInputIds
     * @return integer
     * @throws \PDOException
     */
    public static function deleteByListId(array $arrayInputIds)
    {
        $arrayIds = array();
        foreach ($arrayInputIds as $mixedValue) {
            $mixedValue = (int) $mixedValue;
            if ($mixedValue > 0) {
                $arrayIds[] = $mixedValue;
            }
        }
        $arrayIds = !empty($arrayIds) ? $arrayIds : array(0);

        $strSql = sprintf(
            "DELETE FROM %s.%s WHERE %s IN (%s)",
            static::$hashInfos['database'],
            static::$hashInfos['table'],
            static::$hashInfos['primary_key'],
            implode(',', $arrayIds)
        );

        $objStatement = static::$objDb->prepare($strSql);

        $boolStatus = $objStatement->execute();
        $intRowCount = $objStatement->rowCount();

        // event
        $strHook = sprintf('pdo_model_delete_row_by_ids_%s_%s', static::$hashInfos['database'], static::$hashInfos['table']);
        ObserverHandler::applyHook($strHook, ['data' => $arrayIds, 'success' => $boolStatus, 'count' => $intRowCount]);

        return $intRowCount;
    }

    /**
     * Delete a single row using it's identifier
     * @param $intId
     * @return integer
     * @throws \PDOException
     */
    public static function deleteById($intId)
    {
        return static::deleteByListId(array((int) $intId));
    }

    /**
     * Compute intersection between requested columns and available columns provided by inherited models.
     * Ensures valid queries.
     *
     * @param array $arrayColumns
     * @return array
     */
    protected static function computeFetchedColumns(array $arrayColumns = array())
    {
        $arrayFetchedColumns = array_keys(static::getModelInformation('available_columns'));
        if (!empty($arrayColumns)) {
            $arrayFetchedColumns = array_intersect(array_keys(static::getModelInformation('available_columns')), $arrayColumns);
        }
        return $arrayFetchedColumns;
    }

    /**
     * Fetch one row using its identifier
     *
     * @param $intId
     * @param array $arrayColumns columns that must be fetched (empty <=> all)
     * @return array
     * @throws ModelException|\PDOException
     */
    public static function getById($intId, array $arrayColumns = array())
    {
        if (!Constraint::isInteger($intId, array('min' => 0))) {
            throw new ModelException(sprintf('%s::getById() - Invalid identifier provided (%s:%s)', get_called_class(), (string) $intId, gettype($intId)));
        }

        $arrayFetchedColumns = self::computeFetchedColumns($arrayColumns);

        $strSql = sprintf(
            "SELECT %s FROM %s.%s %s WHERE %s=:%s_id LIMIT 1",
            implode(',', $arrayFetchedColumns),
            static::$hashInfos['database'],
            static::$hashInfos['table'],
            static::$hashInfos['alias'],
            static::$hashInfos['primary_key'],
            static::$hashInfos['alias']
        );

        $objStatement = static::$objDb->prepare($strSql);
        $objStatement->bindValue(':' . static::$hashInfos['primary_key'], $intId, \PDO::PARAM_INT);
        $objStatement->execute();

        return self::singleResult($objStatement);
    }

    /**
     * Fetch several rows using their identifiers
     *
     * @param array $arrayInputIds
     * @param array $arrayColumns columns that must be fetched (empty <=> all)
     * @param array $hashOptions query's options
     * @return array
     * @throws \PDOException
     */
    public static function getByListId(array $arrayInputIds, array $arrayColumns = array(), array $hashOptions = array())
    {
        $arrayIds = array();
        foreach ($arrayInputIds as $intId) {
            if ((int) $intId >= 0) {
                $arrayIds[] = (int) $intId;
            }
        }

        $arrayInParts = array();
        foreach ($arrayIds as $intKey => $intId) {
            $arrayInParts[] = ':' . static::$hashInfos['primary_key'] . '_' . $intKey;
        }

        $arrayFetchedColumns = self::computeFetchedColumns($arrayColumns);

        $strSql = sprintf(
            "SELECT %s FROM %s.%s %s WHERE %s IN (%s) ORDER BY %s ASC",
            implode(',', $arrayFetchedColumns),
            static::$hashInfos['database'],
            static::$hashInfos['table'],
            static::$hashInfos['alias'],
            static::$hashInfos['primary_key'],
            implode(',', $arrayInParts),
            static::$hashInfos['primary_key']
        );

        $objStatement = static::$objDb->prepare($strSql);

        foreach ($arrayIds as $intKey => $intId) {
            $objStatement->bindValue(':' . static::$hashInfos['primary_key'] . '_' . $intKey, $intId, \PDO::PARAM_INT);
        }

        $objStatement->execute();

        return $objStatement->rowCount() > 0 ? $objStatement->fetchAll(\PDO::FETCH_ASSOC) : array();
    }

    /**
     * Fetch several rows using custom conditions, whose options must have a specific format.
     * /!\ Please note this method IS NOT the only way to select data from your database.
     * It is really convenient for simple queries but if you are trying to do something exotic or something
     * ultra-optimized with it, you're wrong.
     *
     * array(
     *  'where|having' => array(
     *      'AFFECTED_COLUMN' => array(
     *          'type'      => OR|AND,
     *          'clause'    => '=', '!=', '<>', '<', '<=', '>', '>=', 'IN', 'LIKE', 'BETWEEN', 'IS', 'IS NOT', 'REGEXP'
     *          'value'     => SCALAR_VALUE|ARRAY(VALUE_#1, VALUE_#2, ...)
     *      ),
     *      ...
     *  ),
     *  'join'  => array(
     *      'left|right|inner|outer' => array([OPTIONAL_FK_#1 => ]AFFECTED_MODEL_#1, AFFECTED_MODEL_#2, ...),
     *      'left|right|inner|outer' => array([OPTIONAL_FK_#1 => ]array('model' => AFFECTED_MODEL_#1, 'alias' => CUSTOM_ALIAS),
     *
     *      ...
     *  ),
     *  'group' => array('AFFECTED_COLUMN_#1', 'AFFECTED_COLUMN_#2, ...),
     *  'order' => array(
     *      'AFFECTED_COLUMN_#1' => ASC|DESC,
     *      ...
     *  )
     *  'limit' => array(
     *      'start' => START_INDEX,
     *      'size'   => SIZE_VALUE
     *  )
     *
     * @param array $arrayColumns
     * @param array $hashOptions where, in, limit, order by
     * @return array
     * @throws ModelException
     *
     *
     * /!\ painful to use, not recommended. Must be improved and simplified
     */
    public static function getGenericList(array $arrayColumns, array $hashOptions = array())
    {
        $arrayJoins = array();
        if (($intKey = array_search('entity_actions', $arrayColumns)) !== false) { // temp fix
            unset($arrayColumns[$intKey]);
        }

        // all available columns' names are prefixed by table alias to avoid ambigous clause
        $hashAvailableColumns = static::getModelInformation('available_columns');
        $arrayAvailableAliases = [static::getModelInformation('alias')];
        $arrayTmpMatches = array();
        foreach ($arrayColumns as $strColumn) {
            // storing all computed columns into available columns list
            if (preg_match('/^.+[ ]+(AS|as|As|aS)[ ]+(.+)$/', $strColumn, $arrayTmpMatches)) {
                if (count($arrayTmpMatches) === 3) {
                    // type is forced to PDO::PARAM_STR but later, the most suitable type is automatically chosen
                    $hashAvailableColumns[$arrayTmpMatches[2]] = \PDO::PARAM_STR;
                }
            }
        }

        /**
         * @var AbstractModel $strModelToJoin
         * @var AbstractModel $strPreviousModelToJoin
         */
        if (isset($hashOptions['join']) && is_array($hashOptions['join']) && !empty($hashOptions['join'])) {

            $arrayJoins = array();
            $strPreviousModelToJoin = get_called_class();
            foreach ($hashOptions['join'] as $strJoinType => $arrayModelToJoin) {
                if (in_array(strtolower($strJoinType), array('left', 'right', 'inner', 'outer'))) {

                    if (!is_array($arrayModelToJoin)) {
                        continue;
                    }

                    foreach ($arrayModelToJoin as $key => $strModelToJoin) {

                        $conditionSup = '';
                        $strForcedAlias = '';

                        // add secondary condition on join
                        if (is_array($strModelToJoin)) {

                            $conditionSup = isset($strModelToJoin['condition']) ? $strModelToJoin['condition'] : '';
                            $strForcedAlias = (isset($strModelToJoin['alias'])) ? $strModelToJoin['alias'] : 'nope';
                            $strModelToJoin = isset($strModelToJoin['model']) ? $strModelToJoin['model'] : 'error_no_model';
                        }

                        if (class_exists($strModelToJoin)) {
                            // automatically joined colon (forced using key on model)
                            $column = $strModelToJoin::getModelInformation('primary_key');
                            if (!is_numeric($key)) {
                                $tmp = explode('.', $key);
                                $key = isset($tmp[1]) ? $tmp[1] : $key;
                                $column = $key;
                            }

                            $arrayJoins[] = sprintf(
                                " %s %s.%s %s ON %s=%s %s",
                                strtoupper($strJoinType) . ' JOIN',
                                $strModelToJoin::getModelInformation('database'),
                                $strModelToJoin::getModelInformation('table'),
                                $strModelToJoin::getModelInformation('alias'),
                                ($strForcedAlias !== '' ? $strForcedAlias : $strPreviousModelToJoin::getModelInformation('alias'))
                                . '.' . $column,
                                $strModelToJoin::getModelInformation('alias')
                                . '.' . $column,
                                $conditionSup

                            );

                            // collect all available columns
                            $strAlias = $strModelToJoin::getModelInformation('alias');
                            $arrayAvailableAliases[] = $strAlias;
                            foreach ($strModelToJoin::getModelInformation('columns') as $strColumn => $intType) {
                                $hashAvailableColumns[sprintf('%s.%s', $strAlias, $strColumn)] = $intType;
                            }
                        } else {
                            throw new ModelException(sprintf('ModelConstraint - [%s JOIN] Non-existent model provided (%s)', strtoupper($strJoinType), $strModelToJoin));
                        }

                        $strPreviousModelToJoin = $strModelToJoin;
                    }
                }
            }
        }

        $strJoin = implode(' ', $arrayJoins);


        $hashWhereInfos = self::computeWhereAndHavingClause($hashOptions, 'where', $hashAvailableColumns, $arrayAvailableAliases);
        $hashWhereGroupInfos = self::computeWhereAndHavingClause($hashOptions, 'where_group', $hashAvailableColumns, $arrayAvailableAliases);
        $hashHavingInfos = self::computeWhereAndHavingClause($hashOptions, 'having', $hashAvailableColumns, $arrayAvailableAliases);
        $hashHavingGroupInfos = self::computeWhereAndHavingClause($hashOptions, 'having_group', $hashAvailableColumns, $arrayAvailableAliases);


        $strGroup = '';
        if (isset($hashOptions['group']) && is_array($hashOptions['group'])) {
            $arrayGroups = array();

            foreach ($hashOptions['group'] as $strColumn) {
                if (is_string($strColumn)) {
                    $arrayGroups[] = $strColumn;
                }
            }
            $strGroup = ' GROUP BY ' . implode(',', $arrayGroups);
        }

        $strOrder = '';
        if (isset($hashOptions['order']) && is_array($hashOptions['order'])) {
            $arrayOrders = array();
            foreach ($hashOptions['order'] as $strColumn => $strOrderType) {
                if (is_string($strColumn) && in_array(strtoupper($strOrderType), array('ASC', 'DESC'))) {
                    $arrayOrders[] = sprintf('%s %s', $strColumn, strtoupper($strOrderType));
                    $strOrder = ' ORDER BY ' . implode(',', $arrayOrders);
                }
            }
        }

        $strLimit = '';
        if (isset($hashOptions['limit']) && is_array($hashOptions['limit'])) {
            $intSize = isset($hashOptions['limit']['size']) ? (int)$hashOptions['limit']['size'] : 0;
            $intStart = isset($hashOptions['limit']['start']) && $intSize !== 0 ? (int)$hashOptions['limit']['start'] : 0;

            if ($intSize !== 0) {
                $strLimit = sprintf(' LIMIT %d', $intSize);
            }
            if ($intSize !== 0 && $intStart !== 0) {
                $strLimit = sprintf(' LIMIT %d, %d', $intStart, $intSize);
            }
        }

        $strTmpWhere = isset($hashOptions['where_group'])
            ? $hashWhereGroupInfos['sql']
            : (isset($hashOptions['where']) ? $hashWhereInfos['sql'] : '');
        $strTmpHaving = isset($hashOptions['having_group'])
            ? $hashHavingGroupInfos['sql']
            : (isset($hashOptions['having']) ? $hashHavingInfos['sql'] : '');

        $arrayFetchedColumns = [];
        foreach ($arrayColumns as $strColumn) {
            if (strpos($strColumn, '.') === false) { // if not specified columns
                $arrayFetchedColumns[] = self::findRealColumnName($strColumn, $arrayAvailableAliases);
            }
        }
        $strColumns = implode(',', $arrayFetchedColumns);

        $strSql = sprintf(
            "SELECT SQL_CALC_FOUND_ROWS %s FROM %s.%s %s %s%s%s%s%s%s",
            $strColumns,
            static::$hashInfos['database'],
            static::$hashInfos['table'],
            static::$hashInfos['alias'],
            $strJoin,
            !empty($strTmpWhere) ? ' WHERE ' . $strTmpWhere : '',
            !empty($strTmpHaving) ? ' HAVING ' . $strTmpHaving : '',
            $strGroup,
            $strOrder,
            $strLimit
        );

        $objStatement = self::$objDb->prepare($strSql);

        foreach ($hashWhereGroupInfos['bind'] as $strParameter => $hashBindInfos) {
            $objStatement->bindValue($strParameter, $hashBindInfos['value'], $hashBindInfos['type']);
        }

        foreach ($hashWhereInfos['bind'] as $strParameter => $hashBindInfos) {
            $objStatement->bindValue($strParameter, $hashBindInfos['value'], $hashBindInfos['type']);
        }

        // /!\ bind type value can be different of \PDO::PARAM_STR for having clause values
        // (see self::findSuitableBindType calls for more details)
        foreach ($hashHavingInfos['bind'] as $strParameter => $hashBindInfos) {
            $objStatement->bindValue($strParameter, $hashBindInfos['value'], $hashBindInfos['type']);
        }

        $objStatement->execute();

        $hashResults = array(
            'results'   => self::multipleResult($objStatement),
            'count'     => $objStatement->rowCount()
        );

        $strCountSql = "SELECT FOUND_ROWS() as nb;";

        $objStatement = self::$objDb->prepare($strCountSql);
        $objStatement->execute();

        $data = self::singleResult($objStatement);
        $hashResults['total'] = (isset($data['nb'])) ? $data['nb'] : 0;

        return $hashResults;
    }

    /**
     * Computes where and having parts from the generic getList() method
     * @param array $hashOptions
     * @param string $strType
     * @param array $hashAvailableColumns
     * @param array $arrayAvailableAliases
     * @param integer $intLevel
     * @return array
     *
     * @throws ModelQueryException
     */
    private static function computeWhereAndHavingClause(array &$hashOptions, $strType, array &$hashAvailableColumns, $arrayAvailableAliases, $intLevel = 0)
    {
        if (!in_array($strType, array('where', 'having', 'where_group', 'having_group'))) {
            return '';
        }

        $strPart = '';
        $hashValuesToBind = array();
        if (isset($hashOptions[$strType]) && is_array($hashOptions[$strType]) && !empty($hashOptions[$strType])) {

            if (in_array($strType, array('where_group', 'having_group'))) {
                $strSubKey = $strType === 'where_group' ? 'where' : 'having';
                $strPart = '';
                foreach ($hashOptions[$strSubKey] as $intConditionIndex => $hashConditions) {
                    if (!isset($hashConditions['conditions'])) {
                        throw new ModelQueryException('%s entries must provide "conditions" data', $strSubKey);
                    }

                    $hashConditionsToCompute = array($strType => $hashConditions['conditions']);
                    $hashConditionPart = self::computeWhereAndHavingClause($hashConditionsToCompute, $strSubKey, $hashAvailableColumns, $arrayAvailableAliases, $intLevel + 1);

                    $strPartType = '';
                    if ($intConditionIndex > 0) { // if not empty then we need to force the use of a logical operator
                        $strPartType = isset($hashConditions['type']) && in_array(strtoupper($hashConditions['type']), array('AND', 'OR'))
                            ? strtoupper($hashConditions['type'])
                            : 'AND';
                    }
                    $strPart .= $strPartType . $hashConditionPart['sql'];
                    $hashValuesToBind = array_merge($hashValuesToBind, $hashConditionPart['bind']);
                }
            } else {
                $arrayWheres = array();
                foreach ($hashOptions[$strType] as $strColumn => $hashWhereOptions) {

                    $strColumn = self::findRealColumnName($strColumn, $arrayAvailableAliases);

                    if (!isset($hashAvailableColumns[$strColumn])) { // if the requested column belongs to available columns
                        continue;
                    }

                    $strPartType = '';
                    if (!empty($arrayWheres)) { // if not empty then we need to force the use of a logical operator
                        $strPartType = isset($hashWhereOptions['type'])
                        && in_array(strtoupper($hashWhereOptions['type']), array('AND', 'OR'))
                            ? strtoupper($hashWhereOptions['type']) : 'AND';
                    }

                    $strPartClause = isset($hashWhereOptions['clause'])
                    && in_array(strtoupper($hashWhereOptions['clause']), array('=', '!=', '<>', '<', '<=', '>', '>=', 'IN', 'LIKE', 'BETWEEN', 'IS', 'IS NOT', 'REGEXP'))
                        ? strtoupper($hashWhereOptions['clause']) : '=';
                    if (array_key_exists('function', $hashWhereOptions)) {
                        $strPartValue = $hashWhereOptions['function'];
                        $arrayWheres[] = sprintf('%s %s %s %s', $strPartType, $strColumn, $strPartClause, $strPartValue);

                    } elseif (isset($hashWhereOptions['value_left']) && isset($hashWhereOptions['value_left'])) { // gestion du between

                        $strPartFieldRadical = ':' . str_replace('.', '_', $strColumn) . '_' . $intLevel . '_';
                        foreach (array('left', 'right') as $intKey => $strKey) {
                            $hashValuesToBind[$strPartFieldRadical . $strKey] = array(
                                'type' => $hashAvailableColumns[$strColumn]['type'],
                                'value' => $hashWhereOptions['value_' . $strKey]
                            );
                        }

                        $arrayWheres[] = sprintf(
                            '%s %s BETWEEN %s',
                            $strPartType,
                            $strColumn,
                            sprintf('%s AND %s', $strPartFieldRadical . 'left', $strPartFieldRadical . 'right')
                        );
                    } elseif (array_key_exists('value', $hashWhereOptions)) {

                        if (is_array($hashWhereOptions['value'])) {

                            $strPartClause = 'IN';
                            $strPartFieldPattern = ':' . str_replace('.', '_', $strColumn) . '_' . $intLevel;
                            $arrayTmp = array();

                            // storage of the multiple field (to bind later)
                            foreach ($hashWhereOptions['value'] as $intKey => $mixedValue) {
                                $strPartField = $strPartFieldPattern . '_' . $intKey;
                                $arrayTmp[] = $strPartField;
                                $hashValuesToBind[$strPartField] = array(
                                    'type' => $hashAvailableColumns[$strColumn]['type'],
                                    'value' => $mixedValue
                                );

                                // special case for having clause (<=> computed columns OTF)
                                if ($strType === 'having') {
                                    $hashValuesToBind[$strPartField]['type'] = self::findSuitableBindType($mixedValue);
                                }
                            }
                            $strPartValue = implode(',', $arrayTmp);
                        } else {
                            $strPartValue = ':' . str_replace('.', '_', $strColumn) . '_' . $intLevel;
                            $hashValuesToBind[$strPartValue] = array(
                                'type' => self::findSuitableBindType($hashWhereOptions['value']),
                                'value' => $hashWhereOptions['value']
                            );

                            // special case for having clause (<=> computed columns OTF)
                            if ($strType === 'having') {
                                $hashValuesToBind[$strPartValue]['type'] = self::findSuitableBindType($hashWhereOptions['value']);
                            }
                        }

                        if ($strPartClause === 'IN') {
                            $arrayWheres[] = sprintf('%s %s IN (%s)', $strPartType, $strColumn, $strPartValue);
                        } else {
                            if (is_null($strPartValue)) {
                                $strPartClause = $strPartClause === '!=' || $strPartClause === '<>' ? 'IS NOT' : 'IS';
                            }
                            $arrayWheres[] = sprintf('%s %s %s %s', $strPartType, $strColumn, $strPartClause, $strPartValue);
                        }
                    }
                }
            }
            if (isset($arrayWheres) && !empty($arrayWheres)) {
                //Si on se trouve dans un group cf where_group
                // le level est toujours > 0
                $strPart = '('.implode(' ', $arrayWheres).')';
            }
        }
        return array(
            'sql' => $strPart,
            'bind' => $hashValuesToBind
        );
    }

    /**
     * Allows builder to find automatically the suitable type for a value (having clause only)
     * @param $mixedValue
     * @return integer
     */
    protected static function findSuitableBindType($mixedValue)
    {
        switch (true) {
            case is_integer($mixedValue):
                return \PDO::PARAM_INT;
                break;
            case is_null($mixedValue):
                return \PDO::PARAM_NULL;
                break;
            default:
            case is_string($mixedValue):
            case is_float($mixedValue):
                return \PDO::PARAM_STR;
                break;
        }
    }

    /**
     * Find real column name (e.g. alias.column) if the column name contains prefix
     * @param string $strColumnName
     * @param array $arrayAvailableAliases authorized aliases
     * @return string
     */
    private static function findRealColumnName($strColumnName, $arrayAvailableAliases)
    {
        if (preg_match('/^([a-z]{3,5})_/', $strColumnName, $arrayTemp) === 1 && in_array($arrayTemp[1], $arrayAvailableAliases)) { // only native columns
            return sprintf('%s.%s', $arrayTemp[1], $strColumnName);
        }
        return $strColumnName;
    }

    /**
     * Allows to begin an sql transaction
     * @return boolean
     */
    final public static function beginTransaction()
    {
        return static::$objDb->beginTransaction();
    }

    /**
     * Allows to close and commit an sql transaction
     * @return boolean
     */
    final public static function commitTransaction()
    {
        return static::$objDb->commit();
    }

    /**
     * Allows to rollback an sql transaction
     * @return boolean
     */
    final public static function rollbackTransaction()
    {
        if (static::isInTransaction()) {
            return static::$objDb->rollBack();
        }
        return false;
    }

    /**
     * Indicates if there is a current transaction
     * @return boolean
     */
    final public static function isInTransaction()
    {
        return static::$objDb->inTransaction();
    }


    /**
     * Allow to create row safely, catch exception(s) and broadcast errors through observer
     * @see AbstractModel::create()
     * @param array $hashData
     * @return integer number of affected rows
     */
    final public static function createSafely(array $hashData)
    {
        $intId = 0;
        try {
            $intId = self::create($hashData);
        } catch (ModelException $e) {
            ObserverHandler::applyHook(
                'pdo_model_create_failure',
                array(
                    'model'     => get_called_class(),
                    'data'      => $hashData,
                    'exception' => $e
                )
            );
        } catch (\PDOException $e) {
            ObserverHandler::applyHook(
                'pdo_model_create_failure',
                array(
                    'model'     => get_called_class(),
                    'data'      => $hashData,
                    'exception' => $e
                )
            );
        }
        return $intId;
    }

    /**
     * Allow to update row safely, catch exception(s) and broadcast errors through observer
     * @see AbstractModel::updateById()
     * @param array $hashData
     * @param array $arrayColumnList columns' list (optional)
     * @return integer number of affected rows
     */
    final public static function updateSafely(array $hashData, array $arrayColumnList = array())
    {
        $intId = 0;
        try {
            $intId = self::updateById($hashData, $arrayColumnList);
        } catch (ModelException $e) {
            ObserverHandler::applyHook(
                'pdo_model_update_failure',
                array(
                    'model'     => get_called_class(),
                    'data'      => $hashData,
                    'exception' => $e
                )
            );
        } catch (\PDOException $e) {
            ObserverHandler::applyHook(
                'pdo_model_update_failure',
                array('model'   => get_called_class(),
                    'data'      => $hashData,
                    'exception' => $e
                )
            );
        }
        return $intId;
    }

    /**
     * Allow to delete several rows safely, using an identifiers list. Catch exception and broadcast errors through observer
     * @see AbstractModel::deleteByListId()
     * @param array $arrayInputIds
     * @return integer
     */
    final public static function deleteSafely(array $arrayInputIds)
    {
        $intCountRows = 0;
        try {
            $intCountRows = self::deleteByListId($arrayInputIds);
        } catch (\PDOException $e) {
            ObserverHandler::applyHook(
                'pdo_model_delete_failure',
                array(
                    'model'     => get_called_class(),
                    'data'      => $arrayInputIds,
                    'exception' => $e
                )
            );
        }
        return $intCountRows;
    }

    /**
     * Allows to execute safely a custom method in a subclass to AbstractModel.
     * [UNSTABLE]
     * @param string $strMethodName
     * @param mixed $mixedParams
     * @return mixed $mixedResult
     */
    final public static function executeSafely($strMethodName, $mixedParams)
    {
        $mixedResult = null;
        try {
            $mixedResult = static::$strMethodName($mixedParams);
        } catch (\PDOException $e) {
            ObserverHandler::applyHook(
                'pdo_model_custom_query_failure',
                array(
                    'model'     => get_called_class(),
                    'method'    => $strMethodName,
                    'data'      => $mixedParams,
                    'exception' => $e
                )
            );
        } catch (ModelException $e) {
            ObserverHandler::applyHook(
                'pdo_model_custom_query_failure',
                array(
                    'model'     => get_called_class(),
                    'method'    => $strMethodName,
                    'data'      => $mixedParams,
                    'exception' => $e
                )
            );
        }
        return $mixedResult;
    }

    /**
     * Fetch single result or empty array (if no results)
     *
     * @param \PDOStatement $objStatement
     * @param int $intFetchType
     * @return array
     */
    protected static function singleResult(\PDOStatement $objStatement, $intFetchType = \PDO::FETCH_ASSOC)
    {
        if ($objStatement->rowCount() > 0) {
            return $objStatement->fetch($intFetchType);
        }
        return [];
    }

    /**
     * Fetch multiple results or empty array (if no results)
     * @param \PDOStatement $objStatement
     * @param int $intFetchType
     * @return array
     */
    public static function multipleResult(\PDOStatement $objStatement, $intFetchType = \PDO::FETCH_ASSOC)
    {
        if ($objStatement->rowCount() > 0) {
            return $objStatement->fetchAll($intFetchType);
        }
        return [];
    }
}

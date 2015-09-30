<?php
namespace App\Models;

use App\Exceptions\ModelException;
use App\Exceptions\ModelQueryException;
use App\Tools\Constraint;
use App\Tools\CustomPDO\CustomPDO;
use App\Tools\Validator;

/**
 * Class AbstractModel.
 * Factorize all basic methods for children models and provide them CRUD operations.
 * @package App\Models
 */
abstract class AbstractModel
{
    /**
     * Mandatory static attributes for children classes.
     * Contains all related table data
     * for this class (AbstractModel) to have abstract code functional.
     * @var array
     */
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
     * @var CustomPDO instance
     */
    protected static $objDb;

    /**
     * Returns information about the current model
     * @param string $strInformation database|alias|primary_key|columns|available_columns
     * @return mixed
     */
    public static function getModelInformation($strInformation = 'table')
    {
        if ($strInformation === 'available_columns') {
            $hashColumns = array();
            foreach (static::$hashInfos['columns'] as $strColumn => $hashColumnsInfos) {
                $hashColumns[$strColumn] = $hashColumnsInfos;
                $hashColumns[static::$hashInfos['alias'] . '.' . $strColumn] = $hashColumnsInfos;
            }
            return $hashColumns;
        } elseif (!in_array($strInformation, array_keys(static::$hashInfos))) {
            $strInformation = 'table';
        }
        return static::$hashInfos[$strInformation];
    }
    
    /**
     * Inject PDO instance to the current model
     * @param CustomPDO $objDb (ideally, should be CustomPDO instance, but can be PDO instance too)
     */
    public static function setPdoInstance($objDb)
    {
	    static::$objDb = $objDb;
    }

    /**
     * Allows to indicate which column needs to have specific type with several options
     * before any insert or update query attempts.
     * This method MUST be overridden to keep coherent models.
     *
     * @param array $hashData
     * @param bool $boolIsUpdating
     *
     * @return boolean
     */
    protected static function validateData(array &$hashData, $boolIsUpdating)
    {
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
                $hashValues[$strColumn] = $hashData[$strColumn];
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

        return $objStatement->execute() ? (int) static::$objDb->lastInsertId() : 0;
    }

    /**
     * Allows to update a row whose an identifier is provided
     * @param array $hashData
     * @param array $arrayColumnList columns' list (optional)
     * @return integer number of affected rows
     * @throws ModelException|\PDOException
     */
    public static function updateById(array $hashData, array $arrayColumnList = array())
    {
        Validator::reset();
        if (!static::validateData($hashData, true)) {
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
        $objStatement->execute();
        return $objStatement->rowCount();
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

        $objStatement->execute();
        return $objStatement->rowCount();
    }

    /**
     * Delete a single row using it's identifier
     * @param $intId
     * @return integer
     * @throws \PDOException
     */
    public static function deleteById($intId)
    {
        return static::deleteByListId(array($intId));
    }

    /**
     * Compute intersection between requested columns and available columns provided by inherited models.
     * Ensures valid queries.
     *
     * @param array $arrayColumns
     * @return array
     */
    private static function computeFetchedColumns(array $arrayColumns)
    {
        $arrayFetchedColumns = array_keys(static::$hashInfos['columns']);
        if (!empty($arrayColumns)) {
            $arrayFetchedColumns = array_intersect(array_keys(static::$hashInfos['columns']), $arrayColumns);
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
            "SELECT %s FROM %s.%s WHERE %s=:%s_id LIMIT 1",
            implode(',', $arrayFetchedColumns),
            static::$hashInfos['database'],
            static::$hashInfos['table'],
            static::$hashInfos['primary_key'],
            static::$hashInfos['alias']
        );

        $objStatement = static::$objDb->prepare($strSql);
        $objStatement->bindValue(':' . static::$hashInfos['primary_key'], $intId, \PDO::PARAM_INT);
        $objStatement->execute();

        return $objStatement->rowCount() > 0 ? $objStatement->fetch(\PDO::FETCH_ASSOC) : array();
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
            "SELECT %s FROM %s.%s WHERE %s IN (%s) ORDER BY %s ASC",
            implode(',', $arrayFetchedColumns),
            static::$hashInfos['database'],
            static::$hashInfos['table'],
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
     *          'clause'    => =|!=|<>|<|<=|>|>=|IN
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
     */
    public static function getGenericList(array $arrayColumns, array $hashOptions = array())
    {
        $arrayJoins = array();
        $strColumns = implode(',', $arrayColumns);

        // all available columns' names are prefixed by table alias to avoid ambigous clause
        $hashAvailableColumns = static::getModelInformation('available_columns');
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
         */

        if (isset($hashOptions['join']) && is_array($hashOptions['join']) && !empty($hashOptions['join'])) {
            $arrayJoins = array();

            foreach ($hashOptions['join'] as $strJoinType => $arrayModelToJoin) {
                if (in_array(strtolower($strJoinType), array('left', 'right', 'inner', 'outer')) && is_array($arrayModelToJoin)) {
                    foreach ($arrayModelToJoin as $key => $strModelToJoin) {
                        $conditionSup = '';

                        // check si il ya une condition suplementaire lors du on ....
                        if (is_array($strModelToJoin)) {
                            $conditionSup = (isset($strModelToJoin['condition']) ? $strModelToJoin['condition'] : '');
                            $strModelToJoin = (isset($strModelToJoin['model']) ? $strModelToJoin['model'] : 'error_no_model');
                        }

                        if (class_exists($strModelToJoin)) {
                            // Au cas ou l'on precise la colonne sur laquelle faire la jointure
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
                                static::getModelInformation('alias')
                                . '.' . $column,
                                $strModelToJoin::getModelInformation('alias')
                                . '.' . $column,
                                $conditionSup

                            );

                            // collect all available columns
                            $strAlias = $strModelToJoin::getModelInformation('alias');
                            foreach ($strModelToJoin::getModelInformation('columns') as $strColumn => $intType) {
                                $hashAvailableColumns[sprintf('%s.%s', $strAlias, $strColumn)] = $intType;
                            }
                        } else {
                            throw new ModelException(sprintf('ModelConstraint - [%s JOIN] Non-existent model provided (%s)', strtoupper($strJoinType), $strModelToJoin));
                        }
                    }
                }
            }

        }

        $strJoin = implode(' ', $arrayJoins);


        $hashWhereInfos = self::computeWhereAndHavingClause($hashOptions, 'where', $hashAvailableColumns);
        $hashWhereGroupInfos = self::computeWhereAndHavingClause($hashOptions, 'where_group', $hashAvailableColumns);
        $hashHavingInfos = self::computeWhereAndHavingClause($hashOptions, 'having', $hashAvailableColumns);
        $hashHavingGroupInfos = self::computeWhereAndHavingClause($hashOptions, 'having_group', $hashAvailableColumns);


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
            'results'   => $objStatement->fetchAll(\PDO::FETCH_ASSOC),
            'count'     => $objStatement->rowCount()
        );
        
        $strCountSql = "SELECT FOUND_ROWS() as nb;";

        $objStatement = self::$objDb->prepare($strCountSql);
        $objStatement->execute();

        $data = $objStatement->fetch(\PDO::FETCH_ASSOC);
        $hashResults['total'] = (isset($data['nb'])) ? $data['nb'] : 0;

        return $hashResults;
    }

    /**
     * Computes where and having parts from the generic getList() method
     * @param array $hashOptions
     * @param string $strType
     * @param array $hashAvailableColumns
     * @param integer $intLevel
     * @return array
     *
     * @throws ModelQueryException
     */
    private static function computeWhereAndHavingClause(array &$hashOptions, $strType, array &$hashAvailableColumns, $intLevel = 0)
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
                    $hashConditionPart = self::computeWhereAndHavingClause($hashConditionsToCompute, $strSubKey, $hashAvailableColumns, $intLevel + 1);

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
                    && in_array(strtoupper($hashWhereOptions['clause']), array('=', '!=', '<>', '<', '<=', '>', '>=', 'IN', 'LIKE', 'BETWEEN', 'IS', 'IS NOT'))
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
}

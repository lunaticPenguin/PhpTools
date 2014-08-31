<?php
namespace App\Models;

use App\Plugins\Tools\Validator;
use Phalcon\DI;
use Phalcon\Mvc\Model;

abstract class FactoryBase
{
    /**
     * Used during a row's update when an extra parameter is passed, listing which fields are authorized for update or not.
     * LIST_WHITE means that the columns passed are only fields which can be updated among all available columns.
     */
    const LIST_WHITE = 0;

    /**
     * Used during a row's update when an extra parameter is passed, listing which columns are authorized for update or not.
     * LIST_BLACK means that the columns passed are only columns which cannot be updated among all available columns.
     */
    const LIST_BLACK = 1;

    /**
     * Mandatory static attributes for children classes.
     * Contains all related table data
     * for this class (FactoryBase) to have abstract code functional.
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
     * @var \PDO instance
     */
    protected static $objDb;

    /**
     * Returns information about the current model
     * @param $strInformation
     * @return mixed
     */
    public static function getModelInformation($strInformation = 'table')
    {
        if (!in_array($strInformation, array_keys(static::$hashInfos))) {
            $strInformation = 'table';
        }
        return static::$hashInfos[$strInformation];
    }
    
    /**
     * Inject PDO instance to the current model
     */
    public static function setPdoInstance(\PDO $objDb)
    {
	self::$objDb = $objDb;
    }

    /**
     * Allows to indicate which column needs to have specific type with several options
     * before any insert or update query attempts.
     * Method to override to keep coherent models.
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
     */
    public static function create(array $hashData)
    {
        Validator::reset();
        if (!static::validateData($hashData, false)) {
            return 0;
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

        $objStatement = self::$objDb->prepare($strSql);

        // values bind
        foreach ($hashValues as $strColumn => $mixedValue) {
            $intType = isset(static::$hashInfos['columns'][$strColumn]['type'])
                ? static::$hashInfos['columns'][$strColumn]['type']
                : \PDO::PARAM_STR;
            $objStatement->bindValue(':' .  $strColumn, $mixedValue, $intType);
        }
        return $objStatement->execute() ? (int) $objDb->lastInsertId() : 0;
    }

    /**
     * Allows to update a row whose an identifier is provided
     * @param array $hashData
     * @param array $arrayList columns' list (optional)
     * @param integer $intFlagList column's list type (included/excluded) (optional)
     * @return integer number of affected rows
     */
    public static function updateById(array $hashData, array $arrayList = array(), $intFlagList = self::LIST_BLACK)
    {
        Validator::reset();
        if (!static::validateData($hashData, true)) {
            return 0;
        }

        $hashSqlParts = array();
        $hashValues = array();
        foreach ($hashData as $strColumn => $mixedValue) {
            if (static::$hashInfos['primary_key'] !== $strColumn && isset(static::$hashInfos['columns'][$strColumn])) {
                if (!empty($arrayList)) {
                    if ($intFlagList === self::LIST_BLACK) {
                        if (!in_array($strColumn, $arrayList)) {
                            $hashSqlParts[$strColumn] = $strColumn . '=:' . $strColumn;
                        }
                    } else {
                        if (in_array($strColumn, $arrayList)) {
                            $hashSqlParts[$strColumn] = $strColumn . '=:' . $strColumn;
                        }
                    }
                } else {
                    $hashSqlParts[$strColumn] = $strColumn . '=:' . $strColumn;
                }
            }
            $hashValues[$strColumn] = $mixedValue;
        }
        $strSql = sprintf(
            "UPDATE %s.%s SET %s WHERE %s=:%s_id",
            static::$hashInfos['database'],
            static::$hashInfos['table'],
            implode(',', $hashSqlParts),
            static::$hashInfos['primary_key'],
            static::$hashInfos['alias']
        );

        $objStatement = self::$objDb->prepare($strSql);

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

        $objStatement = self::$objDb->prepare($strSql);

        $objStatement->execute();
        return $objStatement->rowCount();
    }

    /**
     * Delete a single row using it's identifier
     * @param $intId
     * @return integer
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
     */
    public static function getById($intId, array $arrayColumns = array())
    {
        $intId = (int) $intId;

        $arrayFetchedColumns = self::computeFetchedColumns($arrayColumns);

        $strSql = sprintf(
            "SELECT %s FROM %s.%s WHERE %s=:%s_id LIMIT 1",
            implode(',', $arrayFetchedColumns),
            static::$hashInfos['database'],
            static::$hashInfos['table'],
            static::$hashInfos['primary_key'],
            static::$hashInfos['alias']
        );
        
        $objStatement = self::$objDb->prepare($strSql);
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
        
        $objStatement = self::$objDb->prepare($strSql);

        foreach ($arrayIds as $intKey => $intId) {
            $objStatement->bindValue(':' . static::$hashInfos['primary_key'] . '_' . $intKey, $intId, \PDO::PARAM_INT);
        }

        $objStatement->execute();

        return $objStatement->rowCount() > 0 ? $objStatement->fetchAll(\PDO::FETCH_ASSOC) : array();
    }

    /**
     * Fetch several rows using custom conditions, whose options must have the following format:
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
     *      'left|right|inner|outer' => array(AFFECTED_MODEL_#1, AFFECTED_MODEL_#2, ...),
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
     * )
     *
     *
     * @param array $arrayColumns
     * @param array $hashOptions where, in, limit, order by
     * @return array
     */
    public static function getList(array $arrayColumns, array $hashOptions = array())
    {
        $strColumns = implode(',', $arrayColumns);
        $strJoin = '';
        if (isset($hashOptions['join']) && is_array($hashOptions['join']) && !empty($hashOptions['join'])) {
            $arrayJoins = array();
            foreach ($hashOptions['join'] as $strJoinType => $arrayModelToJoin) {
                if (in_array(strtoupper($strJoinType), array('left', 'right', 'inner', 'outer'))
                    && is_array($arrayModelToJoin)) {
                    foreach ($arrayModelToJoin as $strModelToJoin) {
                        if (class_exists($strModelToJoin)) {
                            $arrayJoins[] = sprintf(
                                " %s %s.%s %s ON %s=%s",
                                strtoupper($strJoinType). ' JOIN',
                                $strModelToJoin::getModelInformation('database'),
                                $strModelToJoin::getModelInformation('table'),
                                $strModelToJoin::getModelInformation('alias'),
                                static::getModelInformation('alias')
                                    . '.' . $strModelToJoin::getModelInformation('primary_key'),
                                $strModelToJoin::getModelInformation('alias')
                                    . '.' . $strModelToJoin::getModelInformation('primary_key')
                            );
                        }
                    }
                }
            }
            $strJoin = implode(' ', $arrayJoins);
        }

        $strWhere = self::computeWhereAndHavingClause($hashOptions, 'where');
        $strHaving = self::computeWhereAndHavingClause($hashOptions, 'having');

        $strOrder = '';
        if (isset($hashOptions['order']) && is_array($hashOptions['order'])) {
            $arrayOrders = array();
            foreach ($hashOptions['order'] as $strColumn => $strOrderType) {
                if (is_string($strColumn) && in_array(strtoupper($strOrderType), array('ASC', 'DESC'))) {
                    $arrayOrders[] = sprintf('%s %s', $strColumn, strtoupper($strOrderType));
                }
            }
            $strOrder = ' ORDER BY ' . implode(',', $arrayOrders);
        }

        $strGroup = '';
        if (isset($hashOptions['group']) && is_array($hashOptions['group'])) {
            $arrayGroups = array();
            foreach ($hashOptions['group'] as $strColumn) {
                if (is_string($strColumn)) {
                    $arrayGroups[] = $strColumn;
                }
            }
            $strGroup = implode(',', $arrayGroups);
        }

        $strLimit = '';
        if (isset($hashOptions['limit']) && is_array($hashOptions['limit'])) {
            $intSize = isset($hashOptions['limit']['size']) ? (int) $hashOptions['limit']['size'] : 0;
            $intStart = isset($hashOptions['limit']['start']) && $intSize !== 0 ? (int) $hashOptions['limit']['start'] : 0;

            if ($intSize !== 0) {
                $strLimit = sprintf(' LIMIT %d', $intSize);
            }
            if ($intSize !== 0 && $intStart !== 0) {
                $strLimit = sprintf(' LIMIT %d, %d', $intStart, $intSize);
            }
        }
        $strSql = sprintf(
            "SELECT %s FROM %s.%s %s %s%s%s%s%s%s",
            $strColumns,
            static::$hashInfos['database'],
            static::$hashInfos['table'],
            static::$hashInfos['alias'],
            $strJoin,
            $strWhere,
            $strHaving,
            $strOrder,
            $strGroup,
            $strLimit
        );
        //var_dump($strSql);

        
        $objStatement = self::$objDb->prepare($strSql);
        $objStatement->execute();

        return array(
            'results'   => $objStatement->fetchAll(\PDO::FETCH_ASSOC),
            'count'     => $objStatement->rowCount()
        );
    }

    /**
     * Computes where and having parts from the generic getList() method
     * @param array $hashOptions
     * @param string $strType
     * @return string
     */
    private static function computeWhereAndHavingClause(array $hashOptions, $strType)
    {
        if (!in_array($strType, array('where', 'having'))) {
            return '';
        }
        $strPart = '';
        if (isset($hashOptions[$strType]) && is_array($hashOptions[$strType]) && !empty($hashOptions[$strType])) {
            $arrayWheres = array();
            foreach ($hashOptions[$strType] as $strColumn => $hashWhereOptions) {
                if (isset($hashWhereOptions['value'])) {
                    $strPartType = '';
                    if (!empty($arrayWheres)) {
                        $strPartType = isset($hashWhereOptions['type'])
                        && in_array(strtoupper($hashWhereOptions['type']), array('AND', 'OR'))
                            ? strtoupper($hashWhereOptions['type']) : 'AND';
                    }

                    $strPartClause = isset($hashWhereOptions['clause'])
                    && in_array(strtoupper($hashWhereOptions['clause']), array('=', '!=', '<>', '<', '<=', '>', '=>', 'IN'))
                        ? strtoupper($hashWhereOptions['clause']) : '=';

                    if (is_array($hashWhereOptions['value'])) {
                        $strPartClause = 'IN';
                        $strPartValue = implode(',', $hashWhereOptions['value']);
                    } else {
                        $strPartValue = $hashWhereOptions['value'];
                    }

                    if ($strPartClause === 'IN') {
                        $arrayWheres[] = sprintf('%s %s IN (%s)', $strPartType, $strColumn, $strPartValue);
                    } else {
                        $arrayWheres[] = sprintf('%s %s %s %s', $strPartType, $strColumn, $strPartClause, (string) $strPartValue);
                    }
                }
            }
            $strPart = sprintf(' %s %s', strtoupper($strType), implode(' ', $arrayWheres));
        }
        return $strPart;
    }
}

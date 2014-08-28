<?php

namespace App\Models;

use App\Plugins\Tools\Validator;
use Phalcon\DI;
use Phalcon\Mvc\Model;

abstract class FactoryBase
{
    /**
     * Mandatory static attribute for children classes.
     * Contains all related table informations
     * to this class (FactoryBase) to have abstract code functional.
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

    public static function setPDOInstance(\PDO $objPDO)
    {
        self::$objDb = $objPDO;
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
     *
     * @return int
     */
    public static function create(array $hashData)
    {
        if (!static::validateData($hashData, false)) {
            Validator::reset();
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

        return $objStatement->execute() ? (int) self::$objDb->lastInsertId() : 0;
    }

    /**
     * Allows to update a row whose an identifier is provided
     * @param array $hashData
     * @return integer number of affected rows
     */
    public static function updateById(array $hashData)
    {
        if (!static::validateData($hashData, true)) {
            Validator::reset();
            return 0;
        }

        $hashSqlParts = array();
        $hashValues = array();
        foreach ($hashData as $strColumn => $mixedValue) {
            if (static::$hashInfos['primary_key'] !== $strColumn && isset(static::$hashInfos['columns'][$strColumn])) {
                $hashSqlParts[$strColumn] = $strColumn . '=:' . $strColumn;
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

    private static function computeFetchedColumns(array $arrayColumns)
    {
        $arrayFetchedColumns = array_keys(static::$hashInfos['columns']);
        if (!empty($arrayColumns)) {
            $arrayFetchedColumns = array_intersect(array_keys(static::$hashInfos['columns']), $arrayColumns);
        }
        return $arrayFetchedColumns;
    }


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
        $objStatement->bindValue(':' . static::$hashInfos['alias'] . '_id', $intId, \PDO::PARAM_INT);
        $objStatement->execute();

        return $objStatement->rowCount() > 0 ? $objStatement->fetch(\PDO::FETCH_ASSOC) : array();
    }

    public static function getByListId(array $arrayIds, array $arrayColumns = array())
    {
        return array();
    }

    public static function getList(array $hashOptions)
    {

    }
}

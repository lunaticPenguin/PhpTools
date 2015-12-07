<?php

namespace App\Models;

use App\Exceptions\ModelException;

interface IModel
{
    /**
     * Allows to create a row in the suitable table
     *
     * @param array $hashData
     * @return int
     * @throws ModelException
     */
    public static function create(array $hashData);

    /**
     * Allows to update a row whose an identifier is provided
     * @param array $hashData
     * @param array $arrayColumnList columns' list (optional)
     * @return integer number of affected rows
     * @throws ModelException
     */
    public static function updateById(array $hashData, array $arrayColumnList = array());

    /**
     * Allow to delete several using an identifiers list
     * @param array $arrayInputIds
     * @return integer
     * @throws ModelException
     */
    public static function deleteByListId(array $arrayInputIds);

    /**
     * Delete a single row using it's identifier
     * @param $intId
     * @return integer
     * @throws ModelException
     */
    public static function deleteById($intId);

    /**
     * Fetch one row using its identifier
     *
     * @param $intId
     * @param array $arrayColumns columns that must be fetched (empty <=> all)
     * @return array
     * @throws ModelException
     */
    public static function getById($intId, array $arrayColumns = array());

    /**
     * Fetch several rows using their identifiers
     *
     * @param array $arrayInputIds
     * @param array $arrayColumns columns that must be fetched (empty <=> all)
     * @param array $hashOptions query's options
     * @return array
     * @throws ModelException
     */
    public static function getByListId(array $arrayInputIds, array $arrayColumns = array(), array $hashOptions = array());

    /**
     * Returns a list with generic behaviour (concerning one model)
     * @param array $arrayColumns
     * @param array $hashOptions
     * @return mixed
     */
    public static function getGenericList(array $arrayColumns, array $hashOptions = array());


    /**
     * Allow to create row safely, catch exception(s) and broadcast errors through observer
     * @see AbstractModel::create()
     * @param array $hashData
     * @return integer number of affected rows
     */
    public static function createSafely(array $hashData);

    /**
     * Allow to update row safely, catch exception(s) and broadcast errors through observer
     * @see AbstractModel::updateById()
     * @param array $hashData
     * @param array $arrayColumnList columns' list (optional)
     * @return integer number of affected rows
     */
    public static function updateSafely(array $hashData, array $arrayColumnList = array());

    /**
     * Allow to delete several rows safely, using an identifiers list. Catch exception and broadcast errors through observer
     * @see AbstractModel::deleteByListId()
     * @param array $arrayInputIds
     * @return integer
     */
    public static function deleteSafely(array $arrayInputIds);

    /**
     * Allows to execute safely a custom method in a subclass to AbstractModel.
     * [UNSTABLE]
     * @param string $strMethodName
     * @param mixed $mixedParams
     * @return mixed $mixedResult
     */
    public static function executeSafely($strMethodName, $mixedParams);
}
<?php
namespace App\Models;

/**
 * Class AbstractModel.
 * Factorize all basic methods for children models and provide them CRUD operations.
 * @package App\Models
 */
abstract class AbstractModel implements IModel
{
    /**
     * Mandatory static attributes for children classes.
     * Contains all related table data
     * for this class (AbstractModel) to have abstract code functional.
     * @var array
     */
    protected static $hashInfos = array();

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
        return false;
    }

    /**
     * Returns information about the current model
     * @param string $strInformation database|alias|primary_key|columns|available_columns|table
     * @return mixed|null
     */
    public static function getModelInformation($strInformation = 'table')
    {
        if (!in_array($strInformation, ['database', 'alias', 'primary_key', 'columns', 'available_columns', 'table'])) {
            return null;
        }
        if ($strInformation === 'available_columns') {
            $hashColumns = array();
            foreach (static::$hashInfos['columns'] as $strColumn => $hashColumnsInfos) {
                $hashColumns[static::$hashInfos['alias'] . '.' . $strColumn] = $hashColumnsInfos;
            }
            return $hashColumns;
        } elseif (!in_array($strInformation, array_keys(static::$hashInfos))) {
            $strInformation = 'table';
        }
        return static::$hashInfos[$strInformation];
    }
}

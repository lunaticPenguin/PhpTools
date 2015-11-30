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
     * Allows to indicate which column needs to have specific type with several options
     * before any insert or update query attempts.
     * This method MUST be overridden or called to keep coherent models.
     *
     * @param array $hashData
     * @param bool $boolIsUpdating
     *
     * @return boolean
     */
    protected static function validateData(array &$hashData, $boolIsUpdating)
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
            if (array_key_exists($strUpdatedAtFieldName, static::$hashInfos['columns'])) {
                $hashData[$strUpdatedAtFieldName] = $strCurrentDatetime;
            }
        }

        return true;
    }
}

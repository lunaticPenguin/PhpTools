<?php
namespace App\Tools;

use App\Models\AbstractModel;
use Symfony\Component\Config\Definition\Exception\Exception;

class ModelConstraint
{
    /**
     * Allows to check a row with a specified value already exists in a column A
     * inside a table B in a database C
     *
     *
     * @return boolean
     */
    public static function alreadyColumnExists($strClassModel, $strColumn, $mixedValue)
    {
        self::checkMappingOptions(array('key_model' => $strClassModel, 'key_column' => $strColumn));

        return $strClassModel::getGenericList(
            array($strClassModel::getModelInformation('columns')['primary_key']),
            array(
                'where' => array(
                    $strColumn => array('clause' => '=', 'value' => $mixedValue),
                ),
                'limit' => array('size' => 1)
            )
        )['count'] === 1;
    }

    /**
     * Allows to check a row with a specified already exists in a column A inside a table B in a database C
     *
     * @param $strClassModel
     * @param $strColumnA
     * @param $mixedValueA
     * @param $strColumnB
     * @param $mixedValueB
     * @return bool
     * @throws \Exception
     */
    public static function alreadyPairColumnExists($strClassModel, $strColumnA, $mixedValueA, $strColumnB, $mixedValueB)
    {
        self::checkMappingOptions(array('key_model' => $strClassModel, 'key_column' => $strColumnA));
        self::checkMappingOptions(array('key_model' => $strClassModel, 'key_column' => $strColumnB));

        return $strClassModel::getGenericList(
            array($strClassModel::getModelInformation('columns')['primary_key']),
            array(
                'where' => array(
                    $strColumnA => array('clause' => '=', 'value' => $mixedValueA),
                    $strColumnB => array('type' => 'AND', 'clause' => '=', 'value' => $mixedValueB),
                ),
                'limit' => array('size' => 1)
            )
        )['count'] === 1;
    }

    /**
     * Check mapping configurations
     * @param array $hashOptions
     * @throws \Exception
     */
    protected static function checkMappingOptions(array $hashOptions)
    {
        if (isset($hashOptions['key_model'])) {
            $hashOptions['model'] = isset($hashOptions[$hashOptions['key_model']]) ? $hashOptions[$hashOptions['key_model']] : '';
        }
        if (isset($hashOptions['key_column'])) {
            $hashOptions['column'] = isset($hashOptions[$hashOptions['key_column']]) ? $hashOptions[$hashOptions['key_column']] : '';
        }
        if (isset($hashOptions['key_value'])) {
            $hashOptions['value'] = isset($hashOptions[$hashOptions['key_value']]) ? $hashOptions[$hashOptions['key_value']] : '';
        }

        $hashOptions['model'] = isset($hashOptions['model']) ? $hashOptions['model'] : '';
        $hashOptions['column'] = isset($hashOptions['column']) ? $hashOptions['column'] : '';
        $hashOptions['value'] = isset($hashOptions['value']) ? $hashOptions['value'] : '';

        if (!class_exists($hashOptions['model'])) {
            throw new \Exception(sprintf('ModelConstraint - Non-existent model provided (%s)', $hashOptions['model']));
        }

        if (!isset($hashOptions['model']::getModelInformation('columns')[$hashOptions['column']])) {
            throw new \Exception(
                sprintf(
                    'ModelConstraint - Non-existent column provided for model %s (%s)',
                    $hashOptions['model'],
                    $hashOptions['column']
                )
            );
        }
    }
}

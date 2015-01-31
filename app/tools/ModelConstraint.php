<?php
namespace App\Tools;
use App\Models\AbstractModel;

/**
 * Class ModelConstraint.
 * Provides methods to check data coherence through models
 *
 * @package App\Tools
 */
class ModelConstraint
{
    /**
     * Allows to check if a row already exists using a single column
     *
     * @param string $strClassModel
     * @param string $strColumn
     * @param mixed $mixedValue
     * @return boolean
     * @throws \Exception
     */
    public static function alreadyColumnExists($strClassModel, $strColumn, $mixedValue)
    {
        /**
         * @var AbstractModel $strClassModel
         */
        self::checkMappingOptions(array('key_model' => $strClassModel, 'key_column' => $strColumn));

        return $strClassModel::getGenericList(
            array($strClassModel::getModelInformation('primary_key')),
            array(
                'where' => array(
                    $strColumn => array('clause' => '=', 'value' => $mixedValue),
                ),
                'limit' => array('size' => 1)
            )
        )['count'] === 1;
    }

    /**
     * Allows to check if a row already exists using two columns
     *
     * @param string $strClassModel
     * @param string $strColumnA
     * @param mixed $mixedValueA
     * @param string $strColumnB
     * @param mixed $mixedValueB
     * @return boolean
     * @throws \Exception
     */
    public static function alreadyPairColumnExists($strClassModel, $strColumnA, $mixedValueA, $strColumnB, $mixedValueB)
    {
        /**
         * @var AbstractModel $strClassModel
         */
        self::checkMappingOptions(array('key_model' => $strClassModel, 'key_column' => $strColumnA));
        self::checkMappingOptions(array('key_model' => $strClassModel, 'key_column' => $strColumnB));

        return $strClassModel::getGenericList(
            array($strClassModel::getModelInformation('primary_key')),
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
        /**
         * @var AbstractModel $strClassModel
         */
        if (isset($hashOptions['key_model'])) {
            $hashOptions['model'] = $hashOptions['key_model'];
        }
        if (isset($hashOptions['key_column'])) {
            $hashOptions['column'] = $hashOptions['key_column'];
        }

        $strClassModel  = isset($hashOptions['model']) ? $hashOptions['model'] : '';
        $hashOptions['column'] = isset($hashOptions['column']) ? $hashOptions['column'] : '';

        if (!class_exists($hashOptions['model'])) {
            throw new \Exception(sprintf('ModelConstraint - Non-existent model provided (%s)', $hashOptions['model']));
        }

        if (!isset($strClassModel::getModelInformation('available_columns')[$hashOptions['column']])) {
            throw new \Exception(
                sprintf(
                    'ModelConstraint - Non-existent column provided for model %s (%s)',
                    $strClassModel,
                    $hashOptions['column']
                )
            );
        }
    }
}

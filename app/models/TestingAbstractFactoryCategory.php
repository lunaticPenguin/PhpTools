<?php
namespace App\Models;

use App\Tools\DataConstraint;
use App\Tools\Validator;
use Phalcon\DI;

class TestingAbstractFactoryCategory extends AbstractModel
{
    protected static $hashInfos = array(
        'database'      => 'phptools',
        'table'         => 'testing_abstract_factory_category',
        'alias'         => 'tafc',
        'primary_key'   => 'tafc_id',
        'columns'   => array(
            'tafc_id'    => array('type' => \PDO::PARAM_INT),
            'tafc_name'    => array('type' => \PDO::PARAM_STR)
        )
    );

    /**
     * Allows to indicate which column needs to have specific type with several options
     * before any insert or update query attempts.
     *
     * @param array $hashData
     * @param bool $boolIsUpdating
     *
     * @return boolean
     */
    protected static function validateData(array &$hashData, $boolIsUpdating)
    {
        Validator::reset();
        DataConstraint::bindData($hashData);
        Validator::validate(DataConstraint::isInteger('taf_id', array('required' => $boolIsUpdating, 'min' => 1)), 'Taf id must be an int > 0');
        Validator::validate(DataConstraint::isString('taf_name', array('required' => !$boolIsUpdating, 'max' => 50)), 'Taf name must be a string 50 char max');

        return Validator::isValid();
    }
}

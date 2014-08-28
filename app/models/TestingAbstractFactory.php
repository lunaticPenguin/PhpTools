<?php

namespace App\Models;

use App\Plugins\Tools\DbConstraint;
use App\Plugins\Tools\Validator;

class TestingAbstractFactory extends FactoryBase
{
    protected static $hashInfos = array(
        'database'      => 'bat_core',
        'table'         => 'testing_abstract_factory',
        'alias'         => 'taf',
        'primary_key'   => 'taf_id',
        'columns'   => array(
            'taf_id'    => array('type' => \PDO::PARAM_INT),
            'taf_name'    => array('type' => \PDO::PARAM_STR),
            'taf_count_int'    => array('type' => \PDO::PARAM_INT),
            'taf_count_float'    => array('type' => \PDO::PARAM_STR)
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
        DbConstraint::bindData($hashData);
        if ($boolIsUpdating) {
            Validator::validate(DbConstraint::isInteger('taf_id', array('min' => 1)), 'Taf id must be an int > 0');
        }
        Validator::validate(DbConstraint::isString('taf_name', array('max' => 50)), 'Taf name must be a string 50 char max');
        Validator::validate(DbConstraint::isInteger('taf_count_int', array('min' => 0)), 'Taf count int must be an int > 0');
        Validator::validate(DbConstraint::isFloat('taf_count_float', array('min' => 0)), 'Taf count float must be an float > 0');

        return Validator::isValid();
    }
}

<?php
namespace App\Models;

use App\Tools\DataConstraint;
use App\Tools\Validator;
use Phalcon\DI;

class TestingAbstractFactory extends FactoryBase
{
    protected static $hashInfos = array(
        'database'      => 'phptools',
        'table'         => 'testing_abstract_factory',
        'alias'         => 'taf',
        'primary_key'   => 'taf_id',
        'columns'   => array(
            'taf_id'    => array('type' => \PDO::PARAM_INT),
            'tafc_id'    => array('type' => \PDO::PARAM_INT),
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
        DataConstraint::bindData($hashData);
        Validator::validate(DataConstraint::isInteger('taf_id', array('required' => $boolIsUpdating, 'min' => 1)), 'Taf id must be an int > 0');
        Validator::validate(DataConstraint::isInteger('tafc_id', array('required' => !$boolIsUpdating, 'min' => 1)), 'Tafc id must be an int > 0');
        Validator::validate(DataConstraint::isString('taf_name', array('required' => !$boolIsUpdating, 'max' => 50)), 'Taf name must be a string 50 char max');
        Validator::validate(DataConstraint::isInteger('taf_count_int', array('required' => !$boolIsUpdating, 'min' => 0)), 'Taf count int must be an int > 0');
        Validator::validate(DataConstraint::isFloat('taf_count_float', array('required' => !$boolIsUpdating, 'min' => 0)), 'Taf count float must be an float > 0');

        return Validator::isValid();
    }

    public static function getEvenNumber()
    {
        $hashOptions = array(
//            'join'  => array(
//                'left' => array('App\Models\TestingAbstractFactoryCategory')
//            ),
//            'where' => array(
////                '(taf_count_int % 2)'  => array(
////                    'clause'    => 'IN',
////                    'value'     => array(0)
////                )
//                'tafc_name'  => array(
//                    'clause' => '=',
//                    'value' => 'ugaduuu'
//                )
//            ),
            // the same using
//            'having'    => array(
//                'modulo'  => array(
//                    'clause'    => '=',
//                    'value'     => 0
//                )
//            )
            'limit' => array(
                'start' => 3,
                'size' => 12
            ),
            'order' => array(
                'modulo'    => 'ASC',
                'taf_count_int'    => 'DESC',
            )
        );
        return self::getList(array('taf_id', 'taf_name', 'taf_count_int', 'taf_count_int % 2 as modulo'), $hashOptions);
    }
}

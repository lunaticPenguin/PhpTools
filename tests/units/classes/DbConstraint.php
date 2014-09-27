<?php

namespace App\Tools\Tests\Units;

$config = include __DIR__ . '/../../../app/config/config.php';
include __DIR__ . '/../../../app/config/loader.php';

use \atoum;
use App\Tools\DbConstraint as DbC;

/**
 * Class DbConstraint
 * @package App\Plugins\Tools\Tests\Units
 */
class DbConstraint extends atoum
{
    public function testIsInteger()
    {
        $this->boolean(DbC::isInteger('plonk'))->isFalse();

        DbC::bindData(array('plonk' => ''));
        $this->boolean(DbC::isInteger('plonk'))->isFalse();
        DbC::bindData(array('plonk' => 1.5));
        $this->boolean(DbC::isInteger('plonk'))->isFalse();
        DbC::bindData(array('plonk' => array()));
        $this->boolean(DbC::isInteger('plonk'))->isFalse();
        DbC::bindData(array('plonk' => new \stdClass()));
        $this->boolean(DbC::isInteger('plonk'))->isFalse();
        DbC::bindData(array('plonk' => true));
        $this->boolean(DbC::isInteger(true))->isFalse();
        DbC::bindData(array('plonk' => false));
        $this->boolean(DbC::isInteger('plonk'))->isFalse();

        DbC::bindData(array('plonk' => -1));
        $this->boolean(DbC::isInteger('plonk'))->isTrue();

        DbC::bindData(array('plonk' => -25));
        $this->boolean(DbC::isInteger('plonk', array('min' => -3, 'max' => -5)))->isFalse();
        DbC::bindData(array('plonk' => -25));
        $this->boolean(DbC::isInteger('plonk', array('min' => -5, 'max' => -3)))->isFalse();

        DbC::bindData(array('plonk' => 5));
        $this->boolean(DbC::isInteger('plonk', array('min' => 1, 'max' => 4)))->isFalse();
        DbC::bindData(array('plonk' => 5));
        $this->boolean(DbC::isInteger('plonk', array('min' => 4, 'max' => 1)))->isFalse();

        DbC::bindData(array('plonk' => 5));
        $this->boolean(DbC::isInteger('plonk', array('min' => 4, 'max' => 6)))->isTrue();
        DbC::bindData(array('plonk' => 5));
        $this->boolean(DbC::isInteger('plonk', array('min' => 6, 'max' => 4)))->isTrue();
    }

    public function testIsFloat()
    {
        $this->boolean(DbC::isFloat('plonk'))->isFalse();

        DbC::bindData(array('plonk' => ''));
        $this->boolean(DbC::isFloat('plonk'))->isFalse();
        DbC::bindData(array('plonk' => array()));
        $this->boolean(DbC::isFloat('plonk'))->isFalse();
        DbC::bindData(array('plonk' => new \stdClass()));
        $this->boolean(DbC::isFloat('plonk'))->isFalse();
        DbC::bindData(array('plonk' => true));
        $this->boolean(DbC::isFloat('plonk'))->isFalse();
        DbC::bindData(array('plonk' => false));
        $this->boolean(DbC::isFloat('plonk'))->isFalse();

        DbC::bindData(array('plonk' => 2.6));
        $this->boolean(DbC::isFloat('plonk'))->isTrue();
        DbC::bindData(array('plonk' => -25.2));
        $this->boolean(DbC::isFloat('plonk', array('min' => -3.1, 'max' => -5.1)))->isFalse();
        DbC::bindData(array('plonk' => -25.2));
        $this->boolean(DbC::isFloat('plonk', array('min' => -5.1, 'max' => -3.1)))->isFalse();

        DbC::bindData(array('plonk' => -3.14));
        $this->boolean(DbC::isFloat('plonk', array('min' => 0)))->isFalse();
        DbC::bindData(array('plonk' => -3.14));
        $this->boolean(DbC::isFloat('plonk', array('min' => -4)))->isTrue();

        DbC::bindData(array('plonk' => 5.6));
        $this->boolean(DbC::isFloat('plonk', array('min' => 1.1,  'max' => 4.1)))->isFalse();
        DbC::bindData(array('plonk' => 5.6));
        $this->boolean(DbC::isFloat('plonk', array('min' => 4.1,  'max' => 1.1)))->isFalse();

        DbC::bindData(array('plonk' => 5.6));
        $this->boolean(DbC::isFloat('plonk', array('min' => 4.1,  'max' => 6.1)))->isTrue();
        DbC::bindData(array('plonk' => 5.6));
        $this->boolean(DbC::isFloat('plonk', array('min' => 6.1,  'max' => 4.1)))->isTrue();
    }

    public function testIsBoolean()
    {
        $this->boolean(DbC::isBoolean('plonk'))->isFalse();

        DbC::bindData(array('plonk' => ''));
        $this->boolean(DbC::isBoolean('plonk'))->isFalse();
        DbC::bindData(array('plonk' => array()));
        $this->boolean(DbC::isBoolean('plonk'))->isFalse();
        DbC::bindData(array('plonk' => new \stdClass()));
        $this->boolean(DbC::isBoolean('plonk'))->isFalse();
        DbC::bindData(array('plonk' => 42));
        $this->boolean(DbC::isBoolean('plonk'))->isFalse();
        DbC::bindData(array('plonk' => 42.1));
        $this->boolean(DbC::isBoolean('plonk'))->isFalse();

        DbC::bindData(array('plonk' => true));
        $this->boolean(DbC::isBoolean('plonk'))->isTrue();
        DbC::bindData(array('plonk' => false));
        $this->boolean(DbC::isBoolean('plonk'))->isTrue();
    }

    public function testIsString()
    {
        $this->boolean(DbC::isString('plonk'))->isFalse();

        DbC::bindData(array('plonk' => 1));
        $this->boolean(DbC::isString('plonk'))->isFalse();
        DbC::bindData(array('plonk' => 1.2));
        $this->boolean(DbC::isString('plonk'))->isFalse();
        DbC::bindData(array('plonk' => array()));
        $this->boolean(DbC::isString('plonk'))->isFalse();
        DbC::bindData(array('plonk' => new \stdClass()));
        $this->boolean(DbC::isString('plonk'))->isFalse();
        DbC::bindData(array('plonk' => true));
        $this->boolean(DbC::isString('plonk'))->isFalse();
        DbC::bindData(array('plonk' => false));
        $this->boolean(DbC::isString('plonk'))->isFalse();

        DbC::bindData(array('plonk' => ''));
        $this->boolean(DbC::isString('plonk'))->isTrue();
        $this->boolean(DbC::isString('plonk', array('min' => 0, 'max' => 0)))->isTrue();
        $this->boolean(DbC::isString('plonk', array('min' => 2, 'max' => 0)))->isTrue();
        $this->boolean(DbC::isString('plonk', array('min' => 0, 'max' => 2)))->isTrue();
        $this->boolean(DbC::isString('plonk', array('min' => 2, 'max' => 1)))->isFalse();
        $this->boolean(DbC::isString('plonk', array('min' => 1, 'max' => 2)))->isFalse();
    }

    public function testIsEmail()
    {
        $this->boolean(DbC::isEmail('plonk'))->isFalse();

        DbC::bindData(array('plonk' => 1));
        $this->boolean(DbC::isEmail('plonk'))->isFalse();
        DbC::bindData(array('plonk' => 1.2));
        $this->boolean(DbC::isEmail('plonk'))->isFalse();
        DbC::bindData(array('plonk' => array()));
        $this->boolean(DbC::isEmail('plonk'))->isFalse();
        DbC::bindData(array('plonk' => new \stdClass()));
        $this->boolean(DbC::isEmail('plonk'))->isFalse();
        DbC::bindData(array('plonk' => true));
        $this->boolean(DbC::isEmail('plonk'))->isFalse();
        DbC::bindData(array('plonk' => false));
        $this->boolean(DbC::isEmail('plonk'))->isFalse();

        DbC::bindData(array('plonk' => ''));
        $this->boolean(DbC::isEmail('plonk'))->isFalse();
        DbC::bindData(array('plonk' => 'bla'));
        $this->boolean(DbC::isEmail('plonk'))->isFalse();
        DbC::bindData(array('plonk' => 'bla@'));
        $this->boolean(DbC::isEmail('plonk'))->isFalse();
        DbC::bindData(array('plonk' => 'bla.fr'));
        $this->boolean(DbC::isEmail('plonk'))->isFalse();
        DbC::bindData(array('plonk' => 'bla.@bla'));
        $this->boolean(DbC::isEmail('plonk'))->isFalse();
        DbC::bindData(array('plonk' => 'bl.a@bla.fr'));
        $this->boolean(DbC::isEmail('plonk'))->isTrue();
        DbC::bindData(array('plonk' => 'bl.[a-@bla.fr'));
        $this->boolean(DbC::isEmail('plonk'))->isFalse();
        DbC::bindData(array('plonk' => 'bl.éa-@bla.fr'));
        $this->boolean(DbC::isEmail('plonk'))->isFalse();
    }

    public function testIsArray()
    {
        $this->boolean(DbC::isArray('plonk'))->isFalse();

        DbC::bindData(array('plonk' => 1));
        $this->boolean(DbC::isArray('plonk'))->isFalse();
        DbC::bindData(array('plonk' => 1.2));
        $this->boolean(DbC::isArray('plonk'))->isFalse();
        DbC::bindData(array('plonk' => new \stdClass()));
        $this->boolean(DbC::isArray('plonk'))->isFalse();
        DbC::bindData(array('plonk' => true));
        $this->boolean(DbC::isArray('plonk'))->isFalse();
        DbC::bindData(array('plonk' => false));
        $this->boolean(DbC::isArray('plonk'))->isFalse();

        DbC::bindData(array('plonk' => array()));
        $this->boolean(DbC::isArray('plonk'))->isTrue();
        DbC::bindData(array('plonk' => array('a', 'b')));
        $this->boolean(DbC::isArray('plonk'))->isTrue();

        DbC::bindData(array('plonk' => array('a', 'b')));
        $this->boolean(DbC::isArray('plonk', array('flag' => DbC::ARRAY_CONTAINS_ALL_VALUES)))->isFalse();
        $this->boolean(DbC::isArray(
                'plonk',
                array('flag' => DbC::ARRAY_CONTAINS_ALL_VALUES, 'values' => array('a')))
        )->isFalse();
        $this->boolean(DbC::isArray(
                'plonk',
                array('flag' => DbC::ARRAY_CONTAINS_ALL_VALUES, 'values' => array('a', 'b')))
        )->isTrue();
        $this->boolean(DbC::isArray(
                'plonk',
                array('flag' => DbC::ARRAY_CONTAINS_ALL_VALUES, 'values' => array('a', 'b', 'c')))
        )->isTrue();

        $this->boolean(DbC::isArray('plonk', array('flag' => DbC::ARRAY_CONTAINS_ALL_KEYS)))->isFalse();
        $this->boolean(DbC::isArray(
                'plonk',
                array('flag' => DbC::ARRAY_CONTAINS_ALL_KEYS, 'keys' => array(0)))
        )->isFalse();
        $this->boolean(DbC::isArray(
                'plonk',
                array('flag' => DbC::ARRAY_CONTAINS_ALL_KEYS, 'keys' => array(0, 1)))
        )->isTrue();

        $this->boolean(DbC::isArray(
                'plonk',
                array(
                    'flag' => DbC::ARRAY_CONTAINS_ALL_KEYS|DbC::ARRAY_CONTAINS_ALL_VALUES,
                    'keys' => array(0),
                    'values' => array('a')
                )
            )
        )->isFalse();
        $this->boolean(DbC::isArray(
                'plonk',
                array(
                    'flag' => DbC::ARRAY_CONTAINS_ALL_KEYS|DbC::ARRAY_CONTAINS_ALL_VALUES,
                    'keys' => array(0, 1),
                    'values' => array('a')
                )
            )
        )->isFalse();
        $this->boolean(DbC::isArray(
                'plonk',
                array(
                    'flag' => DbC::ARRAY_CONTAINS_ALL_KEYS|DbC::ARRAY_CONTAINS_ALL_VALUES,
                    'keys' => array(0),
                    'values' => array('a', 'b')
                )
            )
        )->isFalse();
        $this->boolean(DbC::isArray(
                'plonk',
                array(
                    'flag' => DbC::ARRAY_CONTAINS_ALL_KEYS|DbC::ARRAY_CONTAINS_ALL_VALUES,
                    'keys' => array(0, 1),
                    'values' => array('a', 'b')
                )
            )
        )->isTrue();

        $this->boolean(DbC::isArray(
                'plonk',
                array(
                    'flag' => DbC::ARRAY_IDENTICAL_TO,
                    'other' => array('a')
                )
            )
        )->isFalse();
        $this->boolean(DbC::isArray(
                'plonk',
                array(
                    'flag' => DbC::ARRAY_IDENTICAL_TO,
                    'other' => array('b')
                )
            )
        )->isFalse();
        $this->boolean(DbC::isArray(
                'plonk',
                array(
                    'flag' => DbC::ARRAY_IDENTICAL_TO,
                    'other' => array('b', 'a')
                )
            )
        )->isFalse();
        $this->boolean(DbC::isArray(
                'plonk',
                array(
                    'flag' => DbC::ARRAY_IDENTICAL_TO,
                    'other' => array('a', 'b')
                )
            )
        )->isTrue();

        DbC::bindData(array('plonk' => array('a' => 'a', 2 => 'b')));
        $this->boolean(DbC::isArray(
                'plonk',
                array(
                    'flag' => DbC::ARRAY_IDENTICAL_TO,
                    'other' => array('a', 'b')
                )
            )
        )->isFalse();
        $this->boolean(DbC::isArray(
                'plonk',
                array(
                    'flag' => DbC::ARRAY_IDENTICAL_TO,
                    'other' => array('a', 'b')
                )
            )
        )->isFalse();
        $this->boolean(DbC::isArray(
                'plonk',
                array(
                    'flag' => DbC::ARRAY_IDENTICAL_TO,
                    array('a' => 'a', 2 => 'b')
                )
            )
        )->isTrue();
    }

    public function testIsIdenticalTo()
    {
        $this->boolean(DbC::isIdenticalTo('plik', 'plok'))->isFalse();

        DbC::bindData(array('plik' => 1));
        $this->boolean(DbC::isIdenticalTo('plik', 'plok'))->isFalse();
        DbC::bindData(array()); // reset

        DbC::bindData(array('plok' => 1));
        $this->boolean(DbC::isIdenticalTo('plik', 'plok'))->isFalse();
        DbC::bindData(array()); // reset

        DbC::bindData(array('plok' => 1, 'plik' => '1'));
        $this->boolean(DbC::isIdenticalTo('plik', 'plok'))->isFalse();
        DbC::bindData(array()); // reset

        DbC::bindData(array('plok' => 42, 'plik' => 42));
        $this->boolean(DbC::isIdenticalTo('plik', 'plok'))->isTrue();
        DbC::bindData(array()); // reset
    }

    public function testCheckOptionalParamRequired()
    {
        $this->boolean(DbC::isInteger('plonk', array('required' => true)))->isFalse();
        $this->boolean(DbC::isInteger('plonk', array('required' => false)))->isTrue();

        DbC::bindData(array('plonk' => 42));
        $this->boolean(DbC::isInteger('plonk', array('required' => true)))->isTrue();
        DbC::bindData(array()); // reset

        $this->boolean(DbC::isFloat('plonk', array('required' => true)))->isFalse();
        $this->boolean(DbC::isFloat('plonk', array('required' => false)))->isTrue();

        DbC::bindData(array('plonk' => 3.14));
        $this->boolean(DbC::isFloat('plonk', array('required' => true)))->isTrue();
        DbC::bindData(array()); // reset

        $this->boolean(DbC::isString('plonk', array('required' => true)))->isFalse();
        $this->boolean(DbC::isString('plonk', array('required' => false)))->isTrue();

        DbC::bindData(array('plonk' => 'kikou'));
        $this->boolean(DbC::isString('plonk', array('required' => true)))->isTrue();
        DbC::bindData(array()); // reset

        $this->boolean(DbC::isBoolean('plonk', array('required' => true)))->isFalse();
        $this->boolean(DbC::isBoolean('plonk', array('required' => false)))->isTrue();

        DbC::bindData(array('plonk' => true));
        $this->boolean(DbC::isBoolean('plonk', array('required' => true)))->isTrue();
        DbC::bindData(array()); // reset

        $this->boolean(DbC::isEmail('plonk', array('required' => true)))->isFalse();
        $this->boolean(DbC::isEmail('plonk', array('required' => false)))->isTrue();

        DbC::bindData(array('plonk' => 'bl.a@bla.fr'));
        $this->boolean(DbC::isEmail('plonk', array('required' => true)))->isTrue();
        DbC::bindData(array()); // reset

        $this->boolean(DbC::isArray('plonk', array('required' => true)))->isFalse();
        $this->boolean(DbC::isArray('plonk', array('required' => false)))->isTrue();

        DbC::bindData(array('plonk' => array('Justin Bieber must die.')));
        $this->boolean(DbC::isArray('plonk', array('required' => true)))->isTrue();
        DbC::bindData(array()); // reset
    }

    public function testIsUnique()
    {
        // TODO
        $this->boolean(DbC::isUnique('plonk'))->isFalse();
    }
}

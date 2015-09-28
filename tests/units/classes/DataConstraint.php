<?php

namespace App\Tools\Tests\Units;

$config = include __DIR__ . '/../../../app/config/config.php';
include __DIR__ . '/../../../app/config/loader.php';

use App\Tools\DataConstraint as DC;
use \atoum;

/**
 * Class MConstraint
 * @package App\Plugins\Tools\Tests\Units
 */
class DataConstraint extends atoum
{
    public function testIsInteger()
    {
        $this->boolean(DC::isInteger('plonk'))->isFalse();

        DC::bindData(array('plonk' => ''));
        $this->boolean(DC::isInteger('plonk'))->isFalse();
        DC::bindData(array('plonk' => 1.5));
        $this->boolean(DC::isInteger('plonk'))->isFalse();
        DC::bindData(array('plonk' => array()));
        $this->boolean(DC::isInteger('plonk'))->isFalse();
        DC::bindData(array('plonk' => new \stdClass()));
        $this->boolean(DC::isInteger('plonk'))->isFalse();
        DC::bindData(array('plonk' => true));
        $this->boolean(DC::isInteger(true))->isFalse();
        DC::bindData(array('plonk' => false));
        $this->boolean(DC::isInteger('plonk'))->isFalse();

        DC::bindData(array('plonk' => -1));
        $this->boolean(DC::isInteger('plonk'))->isTrue();

        DC::bindData(array('plonk' => -25));
        $this->boolean(DC::isInteger('plonk', array('min' => -3, 'max' => -5)))->isFalse();
        DC::bindData(array('plonk' => -25));
        $this->boolean(DC::isInteger('plonk', array('min' => -5, 'max' => -3)))->isFalse();

        DC::bindData(array('plonk' => 5));
        $this->boolean(DC::isInteger('plonk', array('min' => 1, 'max' => 4)))->isFalse();
        DC::bindData(array('plonk' => 5));
        $this->boolean(DC::isInteger('plonk', array('min' => 4, 'max' => 1)))->isFalse();

        DC::bindData(array('plonk' => 5));
        $this->boolean(DC::isInteger('plonk', array('min' => 4, 'max' => 6)))->isTrue();
        DC::bindData(array('plonk' => 5));
        $this->boolean(DC::isInteger('plonk', array('min' => 6, 'max' => 4)))->isTrue();
    }

    public function testIsFloat()
    {
        $this->boolean(DC::isFloat('plonk'))->isFalse();

        DC::bindData(array('plonk' => ''));
        $this->boolean(DC::isFloat('plonk'))->isFalse();
        DC::bindData(array('plonk' => array()));
        $this->boolean(DC::isFloat('plonk'))->isFalse();
        DC::bindData(array('plonk' => new \stdClass()));
        $this->boolean(DC::isFloat('plonk'))->isFalse();
        DC::bindData(array('plonk' => true));
        $this->boolean(DC::isFloat('plonk'))->isFalse();
        DC::bindData(array('plonk' => false));
        $this->boolean(DC::isFloat('plonk'))->isFalse();

        DC::bindData(array('plonk' => 2.6));
        $this->boolean(DC::isFloat('plonk'))->isTrue();
        DC::bindData(array('plonk' => -25.2));
        $this->boolean(DC::isFloat('plonk', array('min' => -3.1, 'max' => -5.1)))->isFalse();
        DC::bindData(array('plonk' => -25.2));
        $this->boolean(DC::isFloat('plonk', array('min' => -5.1, 'max' => -3.1)))->isFalse();

        DC::bindData(array('plonk' => -3.14));
        $this->boolean(DC::isFloat('plonk', array('min' => 0)))->isFalse();
        DC::bindData(array('plonk' => -3.14));
        $this->boolean(DC::isFloat('plonk', array('min' => -4)))->isTrue();

        DC::bindData(array('plonk' => 5.6));
        $this->boolean(DC::isFloat('plonk', array('min' => 1.1,  'max' => 4.1)))->isFalse();
        DC::bindData(array('plonk' => 5.6));
        $this->boolean(DC::isFloat('plonk', array('min' => 4.1,  'max' => 1.1)))->isFalse();

        DC::bindData(array('plonk' => 5.6));
        $this->boolean(DC::isFloat('plonk', array('min' => 4.1,  'max' => 6.1)))->isTrue();
        DC::bindData(array('plonk' => 5.6));
        $this->boolean(DC::isFloat('plonk', array('min' => 6.1,  'max' => 4.1)))->isTrue();
    }

    public function testIsBoolean()
    {
        $this->boolean(DC::isBoolean('plonk'))->isFalse();

        DC::bindData(array('plonk' => ''));
        $this->boolean(DC::isBoolean('plonk'))->isFalse();
        DC::bindData(array('plonk' => array()));
        $this->boolean(DC::isBoolean('plonk'))->isFalse();
        DC::bindData(array('plonk' => new \stdClass()));
        $this->boolean(DC::isBoolean('plonk'))->isFalse();
        DC::bindData(array('plonk' => 42));
        $this->boolean(DC::isBoolean('plonk'))->isFalse();
        DC::bindData(array('plonk' => 42.1));
        $this->boolean(DC::isBoolean('plonk'))->isFalse();

        DC::bindData(array('plonk' => true));
        $this->boolean(DC::isBoolean('plonk'))->isTrue();
        DC::bindData(array('plonk' => false));
        $this->boolean(DC::isBoolean('plonk'))->isTrue();
    }

    public function testIsString()
    {
        $this->boolean(DC::isString('plonk'))->isFalse();

        DC::bindData(array('plonk' => 1));
        $this->boolean(DC::isString('plonk'))->isFalse();
        DC::bindData(array('plonk' => 1.2));
        $this->boolean(DC::isString('plonk'))->isFalse();
        DC::bindData(array('plonk' => array()));
        $this->boolean(DC::isString('plonk'))->isFalse();
        DC::bindData(array('plonk' => new \stdClass()));
        $this->boolean(DC::isString('plonk'))->isFalse();
        DC::bindData(array('plonk' => true));
        $this->boolean(DC::isString('plonk'))->isFalse();
        DC::bindData(array('plonk' => false));
        $this->boolean(DC::isString('plonk'))->isFalse();

        DC::bindData(array('plonk' => ''));
        $this->boolean(DC::isString('plonk'))->isTrue();
        $this->boolean(DC::isString('plonk', array('min' => 0, 'max' => 0)))->isTrue();
        $this->boolean(DC::isString('plonk', array('min' => 2, 'max' => 0)))->isTrue();
        $this->boolean(DC::isString('plonk', array('min' => 0, 'max' => 2)))->isTrue();
        $this->boolean(DC::isString('plonk', array('min' => 2, 'max' => 1)))->isFalse();
        $this->boolean(DC::isString('plonk', array('min' => 1, 'max' => 2)))->isFalse();
    }

    public function testIsEmail()
    {
        $this->boolean(DC::isEmail('plonk'))->isFalse();

        DC::bindData(array('plonk' => 1));
        $this->boolean(DC::isEmail('plonk'))->isFalse();
        DC::bindData(array('plonk' => 1.2));
        $this->boolean(DC::isEmail('plonk'))->isFalse();
        DC::bindData(array('plonk' => array()));
        $this->boolean(DC::isEmail('plonk'))->isFalse();
        DC::bindData(array('plonk' => new \stdClass()));
        $this->boolean(DC::isEmail('plonk'))->isFalse();
        DC::bindData(array('plonk' => true));
        $this->boolean(DC::isEmail('plonk'))->isFalse();
        DC::bindData(array('plonk' => false));
        $this->boolean(DC::isEmail('plonk'))->isFalse();

        DC::bindData(array('plonk' => ''));
        $this->boolean(DC::isEmail('plonk'))->isFalse();
        DC::bindData(array('plonk' => 'bla'));
        $this->boolean(DC::isEmail('plonk'))->isFalse();
        DC::bindData(array('plonk' => 'bla@'));
        $this->boolean(DC::isEmail('plonk'))->isFalse();
        DC::bindData(array('plonk' => 'bla.fr'));
        $this->boolean(DC::isEmail('plonk'))->isFalse();
        DC::bindData(array('plonk' => 'bla.@bla'));
        $this->boolean(DC::isEmail('plonk'))->isFalse();
        DC::bindData(array('plonk' => 'bl.a@bla.fr'));
        $this->boolean(DC::isEmail('plonk'))->isTrue();
        DC::bindData(array('plonk' => 'bl.[a-@bla.fr'));
        $this->boolean(DC::isEmail('plonk'))->isFalse();
        DC::bindData(array('plonk' => 'bl.éa-@bla.fr'));
        $this->boolean(DC::isEmail('plonk'))->isFalse();
    }

    public function testIsArray()
    {
        $this->boolean(DC::isArray('plonk'))->isFalse();

        DC::bindData(array('plonk' => 1));
        $this->boolean(DC::isArray('plonk'))->isFalse();
        DC::bindData(array('plonk' => 1.2));
        $this->boolean(DC::isArray('plonk'))->isFalse();
        DC::bindData(array('plonk' => new \stdClass()));
        $this->boolean(DC::isArray('plonk'))->isFalse();
        DC::bindData(array('plonk' => true));
        $this->boolean(DC::isArray('plonk'))->isFalse();
        DC::bindData(array('plonk' => false));
        $this->boolean(DC::isArray('plonk'))->isFalse();

        DC::bindData(array('plonk' => array()));
        $this->boolean(DC::isArray('plonk'))->isTrue();
        DC::bindData(array('plonk' => array('a', 'b')));
        $this->boolean(DC::isArray('plonk'))->isTrue();

        DC::bindData(array('plonk' => array('a', 'b')));
        $this->boolean(DC::isArray('plonk', array('flag' => \App\Tools\Constraint::ARRAY_CONTAINS_ALL_VALUES)))->isFalse();
        $this->boolean(DC::isArray(
                'plonk',
                array('flag' => \App\Tools\Constraint::ARRAY_CONTAINS_ALL_VALUES, 'values' => array('a')))
        )->isFalse();
        $this->boolean(DC::isArray(
                'plonk',
                array('flag' => \App\Tools\Constraint::ARRAY_CONTAINS_ALL_VALUES, 'values' => array('a', 'b')))
        )->isTrue();
        $this->boolean(DC::isArray(
                'plonk',
                array('flag' => \App\Tools\Constraint::ARRAY_CONTAINS_ALL_VALUES, 'values' => array('a', 'b', 'c')))
        )->isTrue();

        $this->boolean(DC::isArray('plonk', array('flag' => \App\Tools\Constraint::ARRAY_CONTAINS_ALL_KEYS)))->isFalse();
        $this->boolean(DC::isArray(
                'plonk',
                array('flag' => \App\Tools\Constraint::ARRAY_CONTAINS_ALL_KEYS, 'keys' => array(0)))
        )->isFalse();
        $this->boolean(DC::isArray(
                'plonk',
                array('flag' => \App\Tools\Constraint::ARRAY_CONTAINS_ALL_KEYS, 'keys' => array(0, 1)))
        )->isTrue();

        $this->boolean(DC::isArray(
                'plonk',
                array(
                    'flag' => \App\Tools\Constraint::ARRAY_CONTAINS_ALL_KEYS|\App\Tools\Constraint::ARRAY_CONTAINS_ALL_VALUES,
                    'keys' => array(0),
                    'values' => array('a')
                )
            )
        )->isFalse();
        $this->boolean(DC::isArray(
                'plonk',
                array(
                    'flag' => \App\Tools\Constraint::ARRAY_CONTAINS_ALL_KEYS|\App\Tools\Constraint::ARRAY_CONTAINS_ALL_VALUES,
                    'keys' => array(0, 1),
                    'values' => array('a')
                )
            )
        )->isFalse();
        $this->boolean(DC::isArray(
                'plonk',
                array(
                    'flag' => \App\Tools\Constraint::ARRAY_CONTAINS_ALL_KEYS|\App\Tools\Constraint::ARRAY_CONTAINS_ALL_VALUES,
                    'keys' => array(0),
                    'values' => array('a', 'b')
                )
            )
        )->isFalse();
        $this->boolean(DC::isArray(
                'plonk',
                array(
                    'flag' => \App\Tools\Constraint::ARRAY_CONTAINS_ALL_KEYS|\App\Tools\Constraint::ARRAY_CONTAINS_ALL_VALUES,
                    'keys' => array(0, 1),
                    'values' => array('a', 'b')
                )
            )
        )->isTrue();

        $this->boolean(DC::isArray(
                'plonk',
                array(
                    'flag' => \App\Tools\Constraint::ARRAY_IDENTICAL_TO,
                    'other' => array('a')
                )
            )
        )->isFalse();
        $this->boolean(DC::isArray(
                'plonk',
                array(
                    'flag' => \App\Tools\Constraint::ARRAY_IDENTICAL_TO,
                    'other' => array('b')
                )
            )
        )->isFalse();
        $this->boolean(DC::isArray(
                'plonk',
                array(
                    'flag' => \App\Tools\Constraint::ARRAY_IDENTICAL_TO,
                    'other' => array('b', 'a')
                )
            )
        )->isFalse();
        $this->boolean(DC::isArray(
                'plonk',
                array(
                    'flag' => \App\Tools\Constraint::ARRAY_IDENTICAL_TO,
                    'other' => array('a', 'b')
                )
            )
        )->isTrue();

        DC::bindData(array('plonk' => array('a' => 'a', 2 => 'b')));
        $this->boolean(DC::isArray(
                'plonk',
                array(
                    'flag' => \App\Tools\Constraint::ARRAY_IDENTICAL_TO,
                    'other' => array('a', 'b')
                )
            )
        )->isFalse();
        $this->boolean(DC::isArray(
                'plonk',
                array(
                    'flag' => \App\Tools\Constraint::ARRAY_IDENTICAL_TO,
                    'other' => array('a', 'b')
                )
            )
        )->isFalse();
        $this->boolean(DC::isArray(
                'plonk',
                array(
                    'flag' => \App\Tools\Constraint::ARRAY_IDENTICAL_TO,
                    array('a' => 'a', 2 => 'b')
                )
            )
        )->isTrue();
    }

    public function testIsIdenticalTo()
    {
        $this->boolean(DC::isIdenticalTo('plik', 'plok'))->isFalse();

        DC::bindData(array('plik' => 1));
        $this->boolean(DC::isIdenticalTo('plik', 'plok'))->isFalse();
        DC::bindData(array()); // reset

        DC::bindData(array('plok' => 1));
        $this->boolean(DC::isIdenticalTo('plik', 'plok'))->isFalse();
        DC::bindData(array()); // reset

        DC::bindData(array('plok' => 1, 'plik' => '1'));
        $this->boolean(DC::isIdenticalTo('plik', 'plok'))->isFalse();
        DC::bindData(array()); // reset

        DC::bindData(array('plok' => 42, 'plik' => 42));
        $this->boolean(DC::isIdenticalTo('plik', 'plok'))->isTrue();
        DC::bindData(array()); // reset
    }

    public function testCheckOptionalParamRequired()
    {
        $this->boolean(DC::isInteger('plonk', array('required' => true)))->isFalse();
        $this->boolean(DC::isInteger('plonk', array('required' => false)))->isTrue();

        DC::bindData(array('plonk' => 42));
        $this->boolean(DC::isInteger('plonk', array('required' => true)))->isTrue();
        DC::bindData(array()); // reset

        $this->boolean(DC::isFloat('plonk', array('required' => true)))->isFalse();
        $this->boolean(DC::isFloat('plonk', array('required' => false)))->isTrue();

        DC::bindData(array('plonk' => 3.14));
        $this->boolean(DC::isFloat('plonk', array('required' => true)))->isTrue();
        DC::bindData(array()); // reset

        $this->boolean(DC::isString('plonk', array('required' => true)))->isFalse();
        $this->boolean(DC::isString('plonk', array('required' => false)))->isTrue();

        DC::bindData(array('plonk' => 'kikou'));
        $this->boolean(DC::isString('plonk', array('required' => true)))->isTrue();
        DC::bindData(array()); // reset

        DC::bindData(array('plonk' => ''));
        $this->boolean(DC::isString('plonk', array('required' => true)))->isFalse();
        DC::bindData(array()); // reset

        $this->boolean(DC::isBoolean('plonk', array('required' => true)))->isFalse();
        $this->boolean(DC::isBoolean('plonk', array('required' => false)))->isTrue();

        DC::bindData(array('plonk' => true));
        $this->boolean(DC::isBoolean('plonk', array('required' => true)))->isTrue();
        DC::bindData(array()); // reset

        $this->boolean(DC::isEmail('plonk', array('required' => true)))->isFalse();
        $this->boolean(DC::isEmail('plonk', array('required' => false)))->isTrue();

        DC::bindData(array('plonk' => 'bl.a@bla.fr'));
        $this->boolean(DC::isEmail('plonk', array('required' => true)))->isTrue();
        DC::bindData(array()); // reset

        $this->boolean(DC::isArray('plonk', array('required' => true)))->isFalse();
        $this->boolean(DC::isArray('plonk', array('required' => false)))->isFalse();

        DC::bindData(array('plonk' => array('Justin Bieber must die.')));
        $this->boolean(DC::isArray('plonk', array('required' => true)))->isTrue();
        DC::bindData(array()); // reset

        DC::bindData(array('plonk' => null));
        $this->boolean(DC::isNull('plonk'))->isTrue();
        $this->boolean(DC::isNull('plonk', array('required' => false)))->isTrue();
        DC::bindData(array()); // reset
    }
}

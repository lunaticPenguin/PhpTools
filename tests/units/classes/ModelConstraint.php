<?php

namespace App\Tools\Tests\Units;

$config = include __DIR__ . '/../../../app/config/config.php';
include __DIR__ . '/../../../app/config/loader.php';

use App\Tools\ModelConstraint as MC;
use \atoum;

/**
 * Class MConstraint
 * @package App\Plugins\Tools\Tests\Units
 */
class ModelConstraint extends atoum
{
    public function testIsInteger()
    {
        $this->boolean(MC::isInteger('plonk'))->isFalse();

        MC::bindData(array('plonk' => ''));
        $this->boolean(MC::isInteger('plonk'))->isFalse();
        MC::bindData(array('plonk' => 1.5));
        $this->boolean(MC::isInteger('plonk'))->isFalse();
        MC::bindData(array('plonk' => array()));
        $this->boolean(MC::isInteger('plonk'))->isFalse();
        MC::bindData(array('plonk' => new \stdClass()));
        $this->boolean(MC::isInteger('plonk'))->isFalse();
        MC::bindData(array('plonk' => true));
        $this->boolean(MC::isInteger(true))->isFalse();
        MC::bindData(array('plonk' => false));
        $this->boolean(MC::isInteger('plonk'))->isFalse();

        MC::bindData(array('plonk' => -1));
        $this->boolean(MC::isInteger('plonk'))->isTrue();

        MC::bindData(array('plonk' => -25));
        $this->boolean(MC::isInteger('plonk', array('min' => -3, 'max' => -5)))->isFalse();
        MC::bindData(array('plonk' => -25));
        $this->boolean(MC::isInteger('plonk', array('min' => -5, 'max' => -3)))->isFalse();

        MC::bindData(array('plonk' => 5));
        $this->boolean(MC::isInteger('plonk', array('min' => 1, 'max' => 4)))->isFalse();
        MC::bindData(array('plonk' => 5));
        $this->boolean(MC::isInteger('plonk', array('min' => 4, 'max' => 1)))->isFalse();

        MC::bindData(array('plonk' => 5));
        $this->boolean(MC::isInteger('plonk', array('min' => 4, 'max' => 6)))->isTrue();
        MC::bindData(array('plonk' => 5));
        $this->boolean(MC::isInteger('plonk', array('min' => 6, 'max' => 4)))->isTrue();
    }

    public function testIsFloat()
    {
        $this->boolean(MC::isFloat('plonk'))->isFalse();

        MC::bindData(array('plonk' => ''));
        $this->boolean(MC::isFloat('plonk'))->isFalse();
        MC::bindData(array('plonk' => array()));
        $this->boolean(MC::isFloat('plonk'))->isFalse();
        MC::bindData(array('plonk' => new \stdClass()));
        $this->boolean(MC::isFloat('plonk'))->isFalse();
        MC::bindData(array('plonk' => true));
        $this->boolean(MC::isFloat('plonk'))->isFalse();
        MC::bindData(array('plonk' => false));
        $this->boolean(MC::isFloat('plonk'))->isFalse();

        MC::bindData(array('plonk' => 2.6));
        $this->boolean(MC::isFloat('plonk'))->isTrue();
        MC::bindData(array('plonk' => -25.2));
        $this->boolean(MC::isFloat('plonk', array('min' => -3.1, 'max' => -5.1)))->isFalse();
        MC::bindData(array('plonk' => -25.2));
        $this->boolean(MC::isFloat('plonk', array('min' => -5.1, 'max' => -3.1)))->isFalse();

        MC::bindData(array('plonk' => -3.14));
        $this->boolean(MC::isFloat('plonk', array('min' => 0)))->isFalse();
        MC::bindData(array('plonk' => -3.14));
        $this->boolean(MC::isFloat('plonk', array('min' => -4)))->isTrue();

        MC::bindData(array('plonk' => 5.6));
        $this->boolean(MC::isFloat('plonk', array('min' => 1.1,  'max' => 4.1)))->isFalse();
        MC::bindData(array('plonk' => 5.6));
        $this->boolean(MC::isFloat('plonk', array('min' => 4.1,  'max' => 1.1)))->isFalse();

        MC::bindData(array('plonk' => 5.6));
        $this->boolean(MC::isFloat('plonk', array('min' => 4.1,  'max' => 6.1)))->isTrue();
        MC::bindData(array('plonk' => 5.6));
        $this->boolean(MC::isFloat('plonk', array('min' => 6.1,  'max' => 4.1)))->isTrue();
    }

    public function testIsBoolean()
    {
        $this->boolean(MC::isBoolean('plonk'))->isFalse();

        MC::bindData(array('plonk' => ''));
        $this->boolean(MC::isBoolean('plonk'))->isFalse();
        MC::bindData(array('plonk' => array()));
        $this->boolean(MC::isBoolean('plonk'))->isFalse();
        MC::bindData(array('plonk' => new \stdClass()));
        $this->boolean(MC::isBoolean('plonk'))->isFalse();
        MC::bindData(array('plonk' => 42));
        $this->boolean(MC::isBoolean('plonk'))->isFalse();
        MC::bindData(array('plonk' => 42.1));
        $this->boolean(MC::isBoolean('plonk'))->isFalse();

        MC::bindData(array('plonk' => true));
        $this->boolean(MC::isBoolean('plonk'))->isTrue();
        MC::bindData(array('plonk' => false));
        $this->boolean(MC::isBoolean('plonk'))->isTrue();
    }

    public function testIsString()
    {
        $this->boolean(MC::isString('plonk'))->isFalse();

        MC::bindData(array('plonk' => 1));
        $this->boolean(MC::isString('plonk'))->isFalse();
        MC::bindData(array('plonk' => 1.2));
        $this->boolean(MC::isString('plonk'))->isFalse();
        MC::bindData(array('plonk' => array()));
        $this->boolean(MC::isString('plonk'))->isFalse();
        MC::bindData(array('plonk' => new \stdClass()));
        $this->boolean(MC::isString('plonk'))->isFalse();
        MC::bindData(array('plonk' => true));
        $this->boolean(MC::isString('plonk'))->isFalse();
        MC::bindData(array('plonk' => false));
        $this->boolean(MC::isString('plonk'))->isFalse();

        MC::bindData(array('plonk' => ''));
        $this->boolean(MC::isString('plonk'))->isTrue();
        $this->boolean(MC::isString('plonk', array('min' => 0, 'max' => 0)))->isTrue();
        $this->boolean(MC::isString('plonk', array('min' => 2, 'max' => 0)))->isTrue();
        $this->boolean(MC::isString('plonk', array('min' => 0, 'max' => 2)))->isTrue();
        $this->boolean(MC::isString('plonk', array('min' => 2, 'max' => 1)))->isFalse();
        $this->boolean(MC::isString('plonk', array('min' => 1, 'max' => 2)))->isFalse();
    }

    public function testIsEmail()
    {
        $this->boolean(MC::isEmail('plonk'))->isFalse();

        MC::bindData(array('plonk' => 1));
        $this->boolean(MC::isEmail('plonk'))->isFalse();
        MC::bindData(array('plonk' => 1.2));
        $this->boolean(MC::isEmail('plonk'))->isFalse();
        MC::bindData(array('plonk' => array()));
        $this->boolean(MC::isEmail('plonk'))->isFalse();
        MC::bindData(array('plonk' => new \stdClass()));
        $this->boolean(MC::isEmail('plonk'))->isFalse();
        MC::bindData(array('plonk' => true));
        $this->boolean(MC::isEmail('plonk'))->isFalse();
        MC::bindData(array('plonk' => false));
        $this->boolean(MC::isEmail('plonk'))->isFalse();

        MC::bindData(array('plonk' => ''));
        $this->boolean(MC::isEmail('plonk'))->isFalse();
        MC::bindData(array('plonk' => 'bla'));
        $this->boolean(MC::isEmail('plonk'))->isFalse();
        MC::bindData(array('plonk' => 'bla@'));
        $this->boolean(MC::isEmail('plonk'))->isFalse();
        MC::bindData(array('plonk' => 'bla.fr'));
        $this->boolean(MC::isEmail('plonk'))->isFalse();
        MC::bindData(array('plonk' => 'bla.@bla'));
        $this->boolean(MC::isEmail('plonk'))->isFalse();
        MC::bindData(array('plonk' => 'bl.a@bla.fr'));
        $this->boolean(MC::isEmail('plonk'))->isTrue();
        MC::bindData(array('plonk' => 'bl.[a-@bla.fr'));
        $this->boolean(MC::isEmail('plonk'))->isFalse();
        MC::bindData(array('plonk' => 'bl.éa-@bla.fr'));
        $this->boolean(MC::isEmail('plonk'))->isFalse();
    }

    public function testIsArray()
    {
        $this->boolean(MC::isArray('plonk'))->isFalse();

        MC::bindData(array('plonk' => 1));
        $this->boolean(MC::isArray('plonk'))->isFalse();
        MC::bindData(array('plonk' => 1.2));
        $this->boolean(MC::isArray('plonk'))->isFalse();
        MC::bindData(array('plonk' => new \stdClass()));
        $this->boolean(MC::isArray('plonk'))->isFalse();
        MC::bindData(array('plonk' => true));
        $this->boolean(MC::isArray('plonk'))->isFalse();
        MC::bindData(array('plonk' => false));
        $this->boolean(MC::isArray('plonk'))->isFalse();

        MC::bindData(array('plonk' => array()));
        $this->boolean(MC::isArray('plonk'))->isTrue();
        MC::bindData(array('plonk' => array('a', 'b')));
        $this->boolean(MC::isArray('plonk'))->isTrue();

        MC::bindData(array('plonk' => array('a', 'b')));
        $this->boolean(MC::isArray('plonk', array('flag' => \App\Tools\Constraint::ARRAY_CONTAINS_ALL_VALUES)))->isFalse();
        $this->boolean(MC::isArray(
                'plonk',
                array('flag' => \App\Tools\Constraint::ARRAY_CONTAINS_ALL_VALUES, 'values' => array('a')))
        )->isFalse();
        $this->boolean(MC::isArray(
                'plonk',
                array('flag' => \App\Tools\Constraint::ARRAY_CONTAINS_ALL_VALUES, 'values' => array('a', 'b')))
        )->isTrue();
        $this->boolean(MC::isArray(
                'plonk',
                array('flag' => \App\Tools\Constraint::ARRAY_CONTAINS_ALL_VALUES, 'values' => array('a', 'b', 'c')))
        )->isTrue();

        $this->boolean(MC::isArray('plonk', array('flag' => \App\Tools\Constraint::ARRAY_CONTAINS_ALL_KEYS)))->isFalse();
        $this->boolean(MC::isArray(
                'plonk',
                array('flag' => \App\Tools\Constraint::ARRAY_CONTAINS_ALL_KEYS, 'keys' => array(0)))
        )->isFalse();
        $this->boolean(MC::isArray(
                'plonk',
                array('flag' => \App\Tools\Constraint::ARRAY_CONTAINS_ALL_KEYS, 'keys' => array(0, 1)))
        )->isTrue();

        $this->boolean(MC::isArray(
                'plonk',
                array(
                    'flag' => \App\Tools\Constraint::ARRAY_CONTAINS_ALL_KEYS|\App\Tools\Constraint::ARRAY_CONTAINS_ALL_VALUES,
                    'keys' => array(0),
                    'values' => array('a')
                )
            )
        )->isFalse();
        $this->boolean(MC::isArray(
                'plonk',
                array(
                    'flag' => \App\Tools\Constraint::ARRAY_CONTAINS_ALL_KEYS|\App\Tools\Constraint::ARRAY_CONTAINS_ALL_VALUES,
                    'keys' => array(0, 1),
                    'values' => array('a')
                )
            )
        )->isFalse();
        $this->boolean(MC::isArray(
                'plonk',
                array(
                    'flag' => \App\Tools\Constraint::ARRAY_CONTAINS_ALL_KEYS|\App\Tools\Constraint::ARRAY_CONTAINS_ALL_VALUES,
                    'keys' => array(0),
                    'values' => array('a', 'b')
                )
            )
        )->isFalse();
        $this->boolean(MC::isArray(
                'plonk',
                array(
                    'flag' => \App\Tools\Constraint::ARRAY_CONTAINS_ALL_KEYS|\App\Tools\Constraint::ARRAY_CONTAINS_ALL_VALUES,
                    'keys' => array(0, 1),
                    'values' => array('a', 'b')
                )
            )
        )->isTrue();

        $this->boolean(MC::isArray(
                'plonk',
                array(
                    'flag' => \App\Tools\Constraint::ARRAY_IDENTICAL_TO,
                    'other' => array('a')
                )
            )
        )->isFalse();
        $this->boolean(MC::isArray(
                'plonk',
                array(
                    'flag' => \App\Tools\Constraint::ARRAY_IDENTICAL_TO,
                    'other' => array('b')
                )
            )
        )->isFalse();
        $this->boolean(MC::isArray(
                'plonk',
                array(
                    'flag' => \App\Tools\Constraint::ARRAY_IDENTICAL_TO,
                    'other' => array('b', 'a')
                )
            )
        )->isFalse();
        $this->boolean(MC::isArray(
                'plonk',
                array(
                    'flag' => \App\Tools\Constraint::ARRAY_IDENTICAL_TO,
                    'other' => array('a', 'b')
                )
            )
        )->isTrue();

        MC::bindData(array('plonk' => array('a' => 'a', 2 => 'b')));
        $this->boolean(MC::isArray(
                'plonk',
                array(
                    'flag' => \App\Tools\Constraint::ARRAY_IDENTICAL_TO,
                    'other' => array('a', 'b')
                )
            )
        )->isFalse();
        $this->boolean(MC::isArray(
                'plonk',
                array(
                    'flag' => \App\Tools\Constraint::ARRAY_IDENTICAL_TO,
                    'other' => array('a', 'b')
                )
            )
        )->isFalse();
        $this->boolean(MC::isArray(
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
        $this->boolean(MC::isIdenticalTo('plik', 'plok'))->isFalse();

        MC::bindData(array('plik' => 1));
        $this->boolean(MC::isIdenticalTo('plik', 'plok'))->isFalse();
        MC::bindData(array()); // reset

        MC::bindData(array('plok' => 1));
        $this->boolean(MC::isIdenticalTo('plik', 'plok'))->isFalse();
        MC::bindData(array()); // reset

        MC::bindData(array('plok' => 1, 'plik' => '1'));
        $this->boolean(MC::isIdenticalTo('plik', 'plok'))->isFalse();
        MC::bindData(array()); // reset

        MC::bindData(array('plok' => 42, 'plik' => 42));
        $this->boolean(MC::isIdenticalTo('plik', 'plok'))->isTrue();
        MC::bindData(array()); // reset
    }

    public function testCheckOptionalParamRequired()
    {
        $this->boolean(MC::isInteger('plonk', array('required' => true)))->isFalse();
        $this->boolean(MC::isInteger('plonk', array('required' => false)))->isTrue();

        MC::bindData(array('plonk' => 42));
        $this->boolean(MC::isInteger('plonk', array('required' => true)))->isTrue();
        MC::bindData(array()); // reset

        $this->boolean(MC::isFloat('plonk', array('required' => true)))->isFalse();
        $this->boolean(MC::isFloat('plonk', array('required' => false)))->isTrue();

        MC::bindData(array('plonk' => 3.14));
        $this->boolean(MC::isFloat('plonk', array('required' => true)))->isTrue();
        MC::bindData(array()); // reset

        $this->boolean(MC::isString('plonk', array('required' => true)))->isFalse();
        $this->boolean(MC::isString('plonk', array('required' => false)))->isTrue();

        MC::bindData(array('plonk' => 'kikou'));
        $this->boolean(MC::isString('plonk', array('required' => true)))->isTrue();
        MC::bindData(array()); // reset

        $this->boolean(MC::isBoolean('plonk', array('required' => true)))->isFalse();
        $this->boolean(MC::isBoolean('plonk', array('required' => false)))->isTrue();

        MC::bindData(array('plonk' => true));
        $this->boolean(MC::isBoolean('plonk', array('required' => true)))->isTrue();
        MC::bindData(array()); // reset

        $this->boolean(MC::isEmail('plonk', array('required' => true)))->isFalse();
        $this->boolean(MC::isEmail('plonk', array('required' => false)))->isTrue();

        MC::bindData(array('plonk' => 'bl.a@bla.fr'));
        $this->boolean(MC::isEmail('plonk', array('required' => true)))->isTrue();
        MC::bindData(array()); // reset

        $this->boolean(MC::isArray('plonk', array('required' => true)))->isFalse();
        $this->boolean(MC::isArray('plonk', array('required' => false)))->isTrue();

        MC::bindData(array('plonk' => array('Justin Bieber must die.')));
        $this->boolean(MC::isArray('plonk', array('required' => true)))->isTrue();
        MC::bindData(array()); // reset
    }

    public function testIsUnique()
    {
        // TODO
        $this->boolean(MC::alreadyExists('plonk'))->isFalse();
    }
}

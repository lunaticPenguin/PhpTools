<?php

namespace App\Tools\Tests\Units;

$config = include __DIR__ . '/../../../app/config/config.php';
include __DIR__ . '/../../../app/config/loader.php';

use \atoum;
use App\Tools\Constraint as C;

/**
 * Class Constraint
 * @package App\Plugins\Tools\Tests\Units
 */
class Constraint extends atoum
{
    public function testIsInteger()
    {
        $this->boolean(C::isInteger(''))->isFalse();
        $this->boolean(C::isInteger(1.5))->isFalse();
        $this->boolean(C::isInteger(array()))->isFalse();
        $this->boolean(C::isInteger(new \stdClass()))->isFalse();
        $this->boolean(C::isInteger(true))->isFalse();
        $this->boolean(C::isInteger(false))->isFalse();

        $this->boolean(C::isInteger(-1))->isTrue();

        $this->boolean(C::isInteger(-25, array('min' => -3, 'max' => -5)))->isFalse();
        $this->boolean(C::isInteger(-25, array('min' => -5, 'max' => -3)))->isFalse();

        $this->boolean(C::isInteger(5, array('min' => 1, 'max' => 4)))->isFalse();
        $this->boolean(C::isInteger(5, array('min' => 4, 'max' => 1)))->isFalse();

        $this->boolean(C::isInteger(5, array('min' => 4, 'max' => 6)))->isTrue();
        $this->boolean(C::isInteger(5, array('min' => 6, 'max' => 4)))->isTrue();
        $this->boolean(C::isInteger('5'))->isTrue();
        $this->boolean(C::isInteger('5', array('min' => 6, 'max' => 4)))->isTrue();
    }

    public function testIsFloat()
    {
        $this->boolean(C::isFloat(''))->isFalse();
        $this->boolean(C::isFloat(array()))->isFalse();
        $this->boolean(C::isFloat(new \stdClass()))->isFalse();
        $this->boolean(C::isFloat(true))->isFalse();
        $this->boolean(C::isFloat(false))->isFalse();

        $this->boolean(C::isFloat(2.6))->isTrue();
        $this->boolean(C::isFloat(-25.2, array('min' => -3.1, 'max' => -5.1)))->isFalse();
        $this->boolean(C::isFloat(-25.2, array('min' => -5.1, 'max' => -3.1)))->isFalse();

        $this->boolean(C::isFloat(-3.14, array('min' => 0)))->isFalse();
        $this->boolean(C::isFloat(-3.14, array('min' => -4)))->isTrue();

        $this->boolean(C::isFloat(5.6, array('min' => 1.1,  'max' => 4.1)))->isFalse();
        $this->boolean(C::isFloat(5.6, array('min' => 4.1,  'max' => 1.1)))->isFalse();

        $this->boolean(C::isFloat(5.6, array('min' => 4.1,  'max' => 6.1)))->isTrue();
        $this->boolean(C::isFloat(5.6, array('min' => 6.1,  'max' => 4.1)))->isTrue();
        $this->boolean(C::isFloat('5.6'))->isTrue();
        $this->boolean(C::isFloat('5.6', array('min' => 6.1,  'max' => 4.1)))->isTrue();
    }

    public function testIsBoolean()
    {
        $this->boolean(C::isBoolean(''))->isFalse();
        $this->boolean(C::isBoolean(array()))->isFalse();
        $this->boolean(C::isBoolean(new \stdClass()))->isFalse();
        $this->boolean(C::isBoolean(42))->isFalse();
        $this->boolean(C::isBoolean(42.1))->isFalse();

        $this->boolean(C::isBoolean(true))->isTrue();
        $this->boolean(C::isBoolean(false))->isTrue();
    }

    public function testIsString()
    {
        $this->boolean(C::isString(1))->isFalse();
        $this->boolean(C::isString(1.2))->isFalse();
        $this->boolean(C::isString(array()))->isFalse();
        $this->boolean(C::isString(new \stdClass()))->isFalse();
        $this->boolean(C::isString(true))->isFalse();
        $this->boolean(C::isString(false))->isFalse();

        $this->boolean(C::isString(''))->isTrue();
        $this->boolean(C::isString('', array('min' => 0, 'max' => 0)))->isTrue();
        $this->boolean(C::isString('', array('min' => 2, 'max' => 0)))->isTrue();
        $this->boolean(C::isString('', array('min' => 0, 'max' => 2)))->isTrue();
        $this->boolean(C::isString('', array('min' => 2, 'max' => 1)))->isFalse();
        $this->boolean(C::isString('', array('min' => 1, 'max' => 2)))->isFalse();

        $this->boolean(C::isString('', array('pattern' => C::STRING_FORMAT_DATE)))->isFalse();
        $this->boolean(C::isString('', array('pattern' => C::STRING_FORMAT_DATETIME)))->isFalse();
        $this->boolean(C::isString('2014-12-24 21:03:16', array('pattern' => C::STRING_FORMAT_DATE)))->isFalse();
        $this->boolean(C::isString('2014-12-24', array('pattern' => C::STRING_FORMAT_DATETIME)))->isFalse();
        $this->boolean(C::isString('2014-12-24', array('pattern' => C::STRING_FORMAT_DATE)))->isTrue();
        $this->boolean(C::isString('2014-12-24 21:03:16', array('pattern' => C::STRING_FORMAT_DATETIME)))->isTrue();
    }

    public function testIsEmail()
    {
        $this->boolean(C::isEmail(1))->isFalse();
        $this->boolean(C::isEmail(1.2))->isFalse();
        $this->boolean(C::isEmail(array()))->isFalse();
        $this->boolean(C::isEmail(new \stdClass()))->isFalse();
        $this->boolean(C::isEmail(true))->isFalse();
        $this->boolean(C::isEmail(false))->isFalse();

        $this->boolean(C::isEmail(''))->isFalse();
        $this->boolean(C::isEmail('bla'))->isFalse();
        $this->boolean(C::isEmail('bla@'))->isFalse();
        $this->boolean(C::isEmail('bla.fr'))->isFalse();
        $this->boolean(C::isEmail('bla.@bla'))->isFalse();
        $this->boolean(C::isEmail('bl.a@bla.fr'))->isTrue();
        $this->boolean(C::isEmail('bl.[a-@bla.fr'))->isFalse();
        $this->boolean(C::isEmail('bl.éa-@bla.fr'))->isFalse();
    }

    public function testIsArray()
    {
        $this->boolean(C::isArray(1))->isFalse();
        $this->boolean(C::isArray(1.2))->isFalse();
        $this->boolean(C::isArray(new \stdClass()))->isFalse();
        $this->boolean(C::isArray(true))->isFalse();
        $this->boolean(C::isArray(false))->isFalse();

        $this->boolean(C::isArray(array()))->isTrue();
        $this->boolean(C::isArray(array('a', 'b')))->isTrue();

        $this->boolean(C::isArray(array('a', 'b'), array('flag' => C::ARRAY_CONTAINS_ALL_VALUES)))->isFalse();
        $this->boolean(C::isArray(
                array('a', 'b'),
                array('flag' => C::ARRAY_CONTAINS_ALL_VALUES, 'values' => array('a')))
        )->isFalse();
        $this->boolean(C::isArray(
                array('a', 'b'),
                array('flag' => C::ARRAY_CONTAINS_ALL_VALUES, 'values' => array('a', 'b')))
        )->isTrue();
        $this->boolean(C::isArray(
                array('a', 'b'),
                array('flag' => C::ARRAY_CONTAINS_ALL_VALUES, 'values' => array('a', 'b', 'c')))
        )->isTrue();

        $this->boolean(C::isArray(array('a', 'b'), array('flag' => C::ARRAY_CONTAINS_ALL_KEYS)))->isFalse();
        $this->boolean(C::isArray(
                array('a', 'b'),
                array('flag' => C::ARRAY_CONTAINS_ALL_KEYS, 'keys' => array(0)))
        )->isFalse();
        $this->boolean(C::isArray(
                array('a', 'b'),
                array('flag' => C::ARRAY_CONTAINS_ALL_KEYS, 'keys' => array(0, 1)))
        )->isTrue();

        $this->boolean(C::isArray(
                array('a', 'b'),
                array(
                    'flag' => C::ARRAY_CONTAINS_ALL_KEYS|C::ARRAY_CONTAINS_ALL_VALUES,
                    'keys' => array(0),
                    'values' => array('a')
                )
            )
        )->isFalse();
        $this->boolean(C::isArray(
                array('a', 'b'),
                array(
                    'flag' => C::ARRAY_CONTAINS_ALL_KEYS|C::ARRAY_CONTAINS_ALL_VALUES,
                    'keys' => array(0, 1),
                    'values' => array('a')
                )
            )
        )->isFalse();
        $this->boolean(C::isArray(
                array('a', 'b'),
                array(
                    'flag' => C::ARRAY_CONTAINS_ALL_KEYS|C::ARRAY_CONTAINS_ALL_VALUES,
                    'keys' => array(0),
                    'values' => array('a', 'b')
                )
            )
        )->isFalse();
        $this->boolean(C::isArray(
                array('a', 'b'),
                array(
                    'flag' => C::ARRAY_CONTAINS_ALL_KEYS|C::ARRAY_CONTAINS_ALL_VALUES,
                    'keys' => array(0, 1),
                    'values' => array('a', 'b')
                )
            )
        )->isTrue();

        $this->boolean(C::isArray(
                array('a', 'b'),
                array(
                    'flag' => C::ARRAY_IDENTICAL_TO,
                    'other' => array('a')
                )
            )
        )->isFalse();
        $this->boolean(C::isArray(
                array('a', 'b'),
                array(
                    'flag' => C::ARRAY_IDENTICAL_TO,
                    'other' => array('b')
                )
            )
        )->isFalse();
        $this->boolean(C::isArray(
                array('a', 'b'),
                array(
                    'flag' => C::ARRAY_IDENTICAL_TO,
                    'other' => array('b', 'a')
                )
            )
        )->isFalse();
        $this->boolean(C::isArray(
                array('a', 'b'),
                array(
                    'flag' => C::ARRAY_IDENTICAL_TO,
                    'other' => array('a', 'b')
                )
            )
        )->isTrue();
        $this->boolean(C::isArray(
                array('a' => 'a', 2 => 'b'),
                array(
                    'flag' => C::ARRAY_IDENTICAL_TO,
                    'other' => array('a', 'b')
                )
            )
        )->isFalse();
        $this->boolean(C::isArray(
                array('a' => 'a', 2 => 'b'),
                array(
                    'flag' => C::ARRAY_IDENTICAL_TO,
                    'other' => array('a', 'b')
                )
            )
        )->isFalse();
        $this->boolean(C::isArray(
                array('a' => 'a', 2 => 'b'),
                array(
                    'flag' => C::ARRAY_IDENTICAL_TO,
                    array('a' => 'a', 2 => 'b')
                )
            )
        )->isTrue();
    }

    public function testIsNull()
    {
        $this->boolean(C::isNull(''))->isFalse();
        $this->boolean(C::isNull(array()))->isFalse();
        $this->boolean(C::isNull(new \stdClass()))->isFalse();
        $this->boolean(C::isNull(42))->isFalse();
        $this->boolean(C::isNull(42.1))->isFalse();

        $this->boolean(C::isNull(true))->isFalse();
        $this->boolean(C::isNull(false))->isFalse();
        $this->boolean(C::isNull(null))->isTrue();
    }
}

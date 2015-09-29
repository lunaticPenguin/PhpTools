<?php

namespace App\Tools\Tests\Units;

$config = include __DIR__ . '/../../../app/config/config.php';
include __DIR__ . '/../../../app/config/loader.php';

use \atoum;
use App\Tools\Validator as V;

class Validator extends atoum
{
    public function testValidate()
    {
        $this->boolean(V::validate(true))->isTrue();
        $this->boolean(V::validate(true, ''))->isTrue();
        $this->boolean(V::validate(true, 'bla'))->isTrue();

        $this->boolean(V::validate(false, 'bli'))->isFalse();
        $this->boolean(V::validate(false, 'blo'))->isFalse();
    }

    public function testIsValid() {

        // no previous test: ok
        $this->boolean(V::isValid())->isTrue();

        // some successful test
        $this->boolean(V::validate(true))->isTrue();
        $this->boolean(V::validate(true, ''))->isTrue();
        $this->boolean(V::validate(true, 'bla'))->isTrue();

        $this->boolean(V::isValid())->isTrue();

        $this->boolean(V::validate(false, 'bli'))->isFalse();

        // after one faulty test, the validator can't anymore affirm it's ok
        $this->boolean(V::isValid())->isFalse();
    }

    public function testGetMessages()
    {
        // some successful test
        $this->boolean(V::validate(true))->isTrue();
        $this->boolean(V::validate(true, ''))->isTrue();
        $this->boolean(V::validate(true, 'bla'))->isTrue();

        // no messages stored if tests are succesful
        $this->array(V::getMessages())->isEmpty();

        $this->boolean(V::validate(false, 'bli'))->isFalse();
        $this->boolean(V::validate(false, 'blo'))->isFalse();

        // after errors, the validator indicates the false status
        $this->boolean(V::isValid())->isFalse();

        // after faulty tests, messages are stored
        $arrayMsg = V::getMessages();
        $this->array($arrayMsg)->isNotEmpty()->hasKeys(array(0, 1))->size->isEqualTo(2);
        $this->string($arrayMsg[0])->isEqualTo('bli');
        $this->string($arrayMsg[1])->isEqualTo('blo');

        // after a call to V::getMessages, the validator is clear and ready for new tests
        $this->boolean(V::isValid())->isTrue();

        // getMessages with specific fieldnames

        V::validate(false, 'bli', 'fieldnameA');
        V::validate(false, 'blo', 'fieldnameB');

        $arrayMsg = V::getMessages('fieldnameA');
        $this->string($arrayMsg[0])->isEqualTo('bli');

        $arrayMsg = V::getMessages('fieldnameB');
        $this->string($arrayMsg[0])->isEqualTo('blo');


        $arrayMsg = V::getMessages();
        $this->array($arrayMsg)->hasKey('fieldnameA');
        $this->array($arrayMsg)->hasKey('fieldnameB');
    }

    public function testHasErrors()
    {
        V::validate(false, 'bli', 'fieldNameA');
        V::validate(false, 'bli', 'fieldNameB');
        V::validate(true, 'bli', 'fieldNameD');

        $this->boolean(V::hasErrors('fieldNameA'))->isTrue();
        $this->boolean(V::hasErrors('fieldNameB'))->isTrue();
        $this->boolean(V::hasErrors('fieldNameC'))->isFalse();
        $this->boolean(V::hasErrors('fieldNameD'))->isFalse();
    }
}

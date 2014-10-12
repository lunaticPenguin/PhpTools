<?php

namespace App\Tools\Tests\Units;

$config = include __DIR__ . '/../../../app/config/config.php';
include __DIR__ . '/../../../app/config/loader.php';
include __DIR__ . "/../../../app/config/services.php";

use App\Models\TestingAbstractFactory as TAF;
use App\Tools\ModelConstraint as MC;
use \atoum;
use Phalcon\DI;


class ModelConstraint extends atoum
{
    private function insertNewRow(array $hashDataToOverride = array())
    {
        $hashData = array_merge(
            array(
                'tafc_id'   => 1,
                'taf_name' => 'test lol',
                'taf_count_int' => 42,
                'taf_count_float' => 42.05
            ),
            $hashDataToOverride
        );
        return TAF::create($hashData);
    }


    public function testAlreadyColumnExists()
    {
        TAF::setPDOInstance(DI::getDefault()->get('db'));

        $intId = $this->insertNewRow();

        $this->exception(function() {
            MC::alreadyColumnExists('', '', '');
        })->isInstanceOf('Exception');

        $this->exception(function() {
            MC::alreadyColumnExists('App\Models\TestingAbstractFactory', '', '');
        })->isInstanceOf('Exception');

        $this->boolean(MC::alreadyColumnExists('App\Models\TestingAbstractFactory', 'taf_id', ''))->isFalse();
        $this->boolean(MC::alreadyColumnExists('App\Models\TestingAbstractFactory', 'taf_id', 0))->isFalse();
        $this->boolean(MC::alreadyColumnExists('App\Models\TestingAbstractFactory', 'taf_id', $intId))->isTrue();

        $this->integer(TAF::deleteById($intId))->isEqualTo(1);
    }

    public function testAlreadyPairColumnExists()
    {
        TAF::setPDOInstance(DI::getDefault()->get('db'));

        $intId = $this->insertNewRow();

        $this->exception(function() {
            MC::alreadyPairColumnExists('', '', '', '', '');
        })->isInstanceOf('Exception');

        $this->exception(function() {
            MC::alreadyPairColumnExists('App\Models\TestingAbstractFactory', '', '', '', '');
        })->isInstanceOf('Exception');

        $this->exception(function() {
            MC::alreadyPairColumnExists('App\Models\TestingAbstractFactory', 'taf_id', '', 'taf_count_in', '');
        })->isInstanceOf('Exception');

        $this->boolean(MC::alreadyPairColumnExists('App\Models\TestingAbstractFactory', 'taf_id', '', 'taf_count_int', ''))->isFalse();
        $this->boolean(MC::alreadyPairColumnExists('App\Models\TestingAbstractFactory', 'taf_id', 0, 'taf_count_int', 0))->isFalse();
        $this->boolean(MC::alreadyPairColumnExists('App\Models\TestingAbstractFactory', 'taf_id', 0, 'taf_count_int', 42))->isFalse();
        $this->boolean(MC::alreadyPairColumnExists('App\Models\TestingAbstractFactory', 'taf_id', $intId, 'taf_count_int', 42))->isTrue();

        $this->integer(TAF::deleteById($intId))->isEqualTo(1);
    }


}

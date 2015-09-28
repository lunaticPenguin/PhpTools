<?php

namespace App\Models\Tests\Units;

$config = include __DIR__ . '/../../../app/config/config.php';
include __DIR__ . '/../../../app/config/loader.php';
include __DIR__ . "/../../../app/config/services.php";

use \atoum;
use App\Models\TestingAbstractFactory as TAF;
use Phalcon\DI;

class TestingAbstractFactory extends atoum
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

    public function testCreate()
    {
        TAF::setPDOInstance(DI::getDefault()->get('db'));
        $this->exception(function() {
            TAF::create(array());
        })->isInstanceOf('App\Exceptions\ModelException');

        $intId = $this->insertNewRow();
        $this->integer($intId)->isGreaterThan(0);

        $this->integer(TAF::deleteById($intId))->isEqualTo(1);
    }

    public function testDeleteById()
    {
        TAF::setPdoInstance(DI::getDefault()->get('db'));
        $intId = $this->insertNewRow();
        $this->integer($intId)->isGreaterThan(0);

        $this->integer(TAF::deleteById(0))->isEqualTo(0);
        $this->integer(TAF::deleteById($intId))->isEqualTo(1);
    }

    public function testDeleteByListId()
    {
        TAF::setPdoInstance(DI::getDefault()->get('db'));
        $this->integer(TAF::deleteByListId(array(-1, 0, -32.4)))->isEqualTo(0);
        $arrayIds = array();
        for ($i = 0 ; $i < 5 ; ++$i) {
            $arrayIds[] = $this->insertNewRow();
        }

        $this->integer(TAF::deleteByListId(array(
            $arrayIds[0],
            $arrayIds[1],
            $arrayIds[2],
        )))->isEqualTo(3);

        $this->integer(TAF::deleteByListId(array(
            $arrayIds[3],
            $arrayIds[4],
        )))->isEqualTo(2);
    }

    public function testUpdate()
    {
        TAF::setPdoInstance(DI::getDefault()->get('db'));
        // test that it fails
        $hashDataUpdated = array(
            'taf_id'            => 0, // wrong id
            'taf_name'          => 'test plonk',
            'taf_count_int'     => 7,
            'taf_count_float'   => 3.14
        );

        $this->exception(function() use ($hashDataUpdated) {
            TAF::updateById($hashDataUpdated);
        })->isInstanceOf('App\Exceptions\ModelException');

        $hashDataUpdated = array(
            'taf_id'            => 1, // imaginary id (but valid value)
            'taf_name'          => 'test plonk',
            'taf_count_int'     => 7,
            'taf_count_float'   => -3.14 // value < 0
        );
        $this->exception(function() use ($hashDataUpdated) {
            TAF::updateById($hashDataUpdated);
        })->isInstanceOf('App\Exceptions\ModelException');

        $intId = $this->insertNewRow();
        $this->integer($intId)->isGreaterThan(0);

        $hashDataUpdated = array(
            'taf_id'            => $intId,
            'taf_name'          => 'test plonk',
            'taf_count_int'     => 7,
            'taf_count_float'   => 3.14
        );

        $this->integer(TAF::updateById($hashDataUpdated))->isEqualTo(1);

        $hashTAFInfos = TAF::getById($intId);

        $this->array($hashTAFInfos)->hasKeys(array('taf_id', 'taf_name', 'taf_count_int', 'taf_count_float'));
        $this->integer((int) $hashTAFInfos['taf_id'])->isEqualTo($intId);
        $this->string($hashTAFInfos['taf_name'])->isEqualTo($hashDataUpdated['taf_name']);
        $this->integer((int) $hashTAFInfos['taf_count_int'])->isEqualTo($hashDataUpdated['taf_count_int']);
        $this->float((float)$hashTAFInfos['taf_count_float'])->isEqualTo($hashDataUpdated['taf_count_float']);

        // test with specific columns

        $hashTAFInfos = TAF::getById($intId, array('plonk', 'taf_name'));

        $this->array($hashTAFInfos)->hasKey('taf_name')->notHasKeys(array('taf_id', 'taf_count_int', 'taf_count_float'));
        $this->string($hashTAFInfos['taf_name'])->isEqualTo($hashDataUpdated['taf_name']);

        $this->integer(TAF::deleteById($intId))->isEqualTo(1);
    }

    public function testGetById()
    {
        TAF::setPdoInstance(DI::getDefault()->get('db'));
        $intId = $this->insertNewRow();
        $this->integer($intId)->isGreaterThan(0);

        $hashTAFInfos = TAF::getById($intId);

        $this->array($hashTAFInfos)->hasKeys(array('taf_id', 'taf_name', 'taf_count_int', 'taf_count_float'));
        $this->integer((int) $hashTAFInfos['taf_id'])->isEqualTo($intId);
        $this->string($hashTAFInfos['taf_name'])->isEqualTo('test lol');
        $this->integer((int) $hashTAFInfos['taf_count_int'])->isEqualTo(42);
        $this->float((float) $hashTAFInfos['taf_count_float'])->isEqualTo(42.05);

        $this->integer(TAF::deleteById($intId))->isEqualTo(1);
    }

    public function testGetByListId()
    {
        TAF::setPdoInstance(DI::getDefault()->get('db'));
        $arrayIds = array();
        $arrayNames = array('plonk', 'plink', 'plunk');
        foreach ($arrayNames as $strName) {
            $intId = $this->insertNewRow(array('taf_name' => $strName));
            $this->integer($intId)->isGreaterThan(0);
            $arrayIds[] = $intId;
        }

        $arrayTAFInfos = TAF::getByListId($arrayIds);
        $this->array($arrayTAFInfos)->isNotEmpty()->hasKeys(array(0, 1, 2));

        foreach ($arrayNames as $intKey => $strName) {
            $this->array($arrayTAFInfos[$intKey])->hasKeys(array('taf_id', 'taf_name', 'taf_count_int', 'taf_count_float'));
            $this->integer((int) $arrayTAFInfos[$intKey]['taf_id'])->isEqualTo($arrayIds[$intKey]);
            $this->string($arrayTAFInfos[$intKey]['taf_name'])->isEqualTo($strName);
            $this->integer((int) $arrayTAFInfos[$intKey]['taf_count_int'])->isEqualTo(42);
            $this->float((float) $arrayTAFInfos[$intKey]['taf_count_float'])->isEqualTo(42.05);
        }
        $this->integer(TAF::deleteByListId($arrayIds))->isEqualTo(3);
    }

    public function testGetModelInformation()
    {
        $this->string(TAF::getModelInformation())->isEqualTo('testing_abstract_factory');
        $this->string(TAF::getModelInformation('wjdfhj'))->isEqualTo('testing_abstract_factory')
        ;
        $this->string(TAF::getModelInformation('database'))->isEqualTo('phptools');
        $this->string(TAF::getModelInformation('table'))->isEqualTo('testing_abstract_factory');
        $this->string(TAF::getModelInformation('primary_key'))->isEqualTo('taf_id');
        $this->string(TAF::getModelInformation('alias'))->isEqualTo('taf');
        $this->array(TAF::getModelInformation('columns'))
            ->isNotEmpty()
            ->hasKeys(array(
                    'taf_id', 'tafc_id', 'taf_name', 'taf_count_int', 'taf_count_float'
                )
            );
    }

    public function testGetGenericList()
    {
        TAF::setPdoInstance(DI::getDefault()->get('db'));
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
            'having'    => array(
                'modulo'  => array(
                    'clause'    => '=',
                    'value'     => null
                )
            )
//            'limit' => array(
//                'start' => 3,
//                'size' => 12
//            ),
//            'order' => array(
//                'modulo'    => 'ASC',
//                'taf_count_int'    => 'DESC',
//            )
        );

        $hashResult = TAF::getGenericList(array('taf_id', 'taf_name', 'taf_count_int', 'taf_count_int % 2 as modulo'), $hashOptions);
//        $this->array($hashResult)->hasSize(2)->hasKeys(array('results', 'count'));
//        $this->integer($hashResult['count'])->isEqualTo(0);
//        $this->array($hashResult['results'])->isEmpty();
    }
}

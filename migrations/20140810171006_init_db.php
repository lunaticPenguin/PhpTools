<?php

use Phinx\Migration\AbstractMigration;

/**
 * Class InitDb
 * Only for units tests
 */
class InitDb extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        /*
         * ACCOUNT
         */
        $objTable = $this->table('testing_abstract_factory', array('id' => 'taf_id'));
        $objTable->addColumn('tafc_id', 'integer', array('null' => false))
            ->addColumn('taf_name', 'string', array('null' => false))
            ->addColumn('taf_count_int', 'integer', array('null' => false))
            ->addColumn('taf_count_float', 'float', array('null' => false))

            ->save();

        /*
         * ACL
         */
        $objTable = $this->table('testing_abstract_factory_category', array('id' => 'tafc_id'));
        $objTable->addColumn('tafc_name', 'string', array('limit' => 50, 'null' => false))
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
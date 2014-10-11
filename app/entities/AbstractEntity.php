<?php
namespace App\Entities;

/**
 * Class AbstractEntity.
 * Should be a base for all entities that used models.
 *
 * @package App\Entities
 */
abstract class AbstractEntity
{
    const PROCESS_CREATE = 0;
    const PROCESS_UPDATE = 1;

    /**
     * Allows to check data coherence during a process save.
     * A type of save can be provided.
     * @param array $hashData
     * @param integer $intType
     * @return bool
     */
    protected static function checkSave($hashData, $intType = self::PROCESS_CREATE)
    {

    }

    /**
     * Process save operation for the current entity.
     * This method should call self::checkSave() method
     * @param array $hashData
     * @param integer $intType
     * @return mixed
     */
    protected static function processSave($hashData, $intType = self::PROCESS_CREATE)
    {

    }
}

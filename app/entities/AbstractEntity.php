<?php
namespace App\Entities;
use App\Exceptions\EntityException;

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

    protected $intId = 0;

    /**
     * Construct a new entity that used models layer
     *
     * @param integer $intId
     * @throws EntityException
     */
    public function __construct($intId = 0)
    {
        $intId = (integer) $intId;
        if ($intId < 0) {
            throw new EntityException(sprintf('Wrong entity id given (%d)', $intId));
        }
        $this->intId = $intId;
    }

    /**
     * Allows to save changes for the current entity
     * @param $hashData
     * @param int $intType
     * @return boolean
     */
    protected abstract function processSave($hashData, $intType = self::PROCESS_CREATE);

    /**
     * Allows to check data coherence during a process save.
     * A type of save can be provided.
     * @param array $hashData
     * @param integer $intType
     * @return boolean
     */
    protected abstract function checkSave($hashData, $intType = self::PROCESS_CREATE);

    /**
     * Process save operation for the current entity.
     *
     * @param array $hashData
     * @param integer $intType
     * @return boolean
     */
    final public function save($hashData, $intType = self::PROCESS_CREATE)
    {
        if (!$this->checkSave($hashData, $intType)) {
            return false;
        }
        return $this->processSave($hashData);
    }
}

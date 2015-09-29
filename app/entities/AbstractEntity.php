<?php
namespace App\Entities;
use App\Exceptions\EntityException;
use Traversable;

/**
 * Class AbstractEntity.
 * Should be a base for all entities that used models.
 *
 * This layer provides entity as array to get an easier handling
 *
 * @package App\Entities
 */
abstract class AbstractEntity implements \IteratorAggregate, \ArrayAccess
{
    const PROCESS_CREATE = 0;
    const PROCESS_UPDATE = 1;

    protected $intId = 0;

    /**
     * Entity attributes
     * @var array
     */
    protected $hashFields = array();

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

     /**
      * (PHP 5 &gt;= 5.0.0)<br/>
      * Retrieve an external iterator
      * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
      * @return Traversable An instance of an object implementing <b>Iterator</b> or
      * <b>Traversable</b>
      */
     public function getIterator()
     {
         return new \ArrayIterator($this->hashFields);
     }

     /**
      * (PHP 5 &gt;= 5.0.0)<br/>
      * Whether a offset exists
      * @link http://php.net/manual/en/arrayaccess.offsetexists.php
      * @param mixed $offset <p>
      * An offset to check for.
      * </p>
      * @return boolean true on success or false on failure.
      * </p>
      * <p>
      * The return value will be casted to boolean if non-boolean was returned.
      */
     public function offsetExists($offset)
     {
         return array_key_exists($offset, $this->hashFields);
     }

     /**
      * (PHP 5 &gt;= 5.0.0)<br/>
      * Offset to retrieve
      * @link http://php.net/manual/en/arrayaccess.offsetget.php
      * @param mixed $offset <p>
      * The offset to retrieve.
      * </p>
      * @return mixed Can return all value types.
      */
     public function offsetGet($offset)
     {
         return $this->offsetExists($offset) ? $this->hashFields[$offset] : null;
     }

     /**
      * (PHP 5 &gt;= 5.0.0)<br/>
      * Offset to set
      * @link http://php.net/manual/en/arrayaccess.offsetset.php
      * @param mixed $offset <p>
      * The offset to assign the value to.
      * </p>
      * @param mixed $value <p>
      * The value to set.
      * </p>
      * @return void
      */
     public function offsetSet($offset, $value)
     {
         if (!is_null($offset)) {
             $this->hashFields[$offset] = $value;
         }
     }

     /**
      * (PHP 5 &gt;= 5.0.0)<br/>
      * Offset to unset
      * @link http://php.net/manual/en/arrayaccess.offsetunset.php
      * @param mixed $offset <p>
      * The offset to unset.
      * </p>
      * @return void
      */
     public function offsetUnset($offset)
     {
         if ($this->offsetExists($offset)) {
             unset($this->hashFields[$offset]);
         }
     }
 }

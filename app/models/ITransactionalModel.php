<?php

namespace App\Models;

/**
 * Interface ITransactionalModel.
 * Defines behaviour for transactional model
 *
 * @package App\Models
 */
interface ITransactionalModel
{
    /**
     * Allows to begin an sql transaction
     * @return boolean
     */
    public static function beginTransaction();

    /**
     * Allows to close and commit an sql transaction
     * @return boolean
     */
    public static function commitTransaction();

    /**
     * Allows to rollback an sql transaction
     * @return boolean
     */
    public static function rollbackTransaction();

    /**
     * Indicates if there is a current transaction
     * @return boolean
     */
    public static function isInTransaction();
}
<?php

namespace App\Entities;

/**
 * Defines security methods to provide security restrictions to an entity bound to the security system.
 *
 * Interface ISecurityEntity
 * @package App\Entities
 *
 * @see App\Tools\Security
 */
interface ISecurityEntity
{
    /**
     * Returns object accesses list
     * @return mixed
     */
    public function getACO();

    /**
     * Returns accesses list
     * @return mixed
     */
    public function getACL();

    /**
     * Returns identifier for accessed object
     * @return mixed
     */
    public function getID();
}

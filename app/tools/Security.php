<?php

namespace App\Tools;

use App\Entities\ISecurityEntity;

class Security
{
    /**
     * Possible object accesses
     */
    const PERMISSION_READ = 1;
    const PERMISSION_WRITE = 2;


    protected static $hashAccesses = array();

    protected static $hashObjectsAccesses = array();

    /**
     * Loads standards accesses (areas)
     * @param ISecurityEntity $objUserEntity
     */
    public static function loadAccesses(ISecurityEntity $objUserEntity)
    {
        self::$hashAccesses = $objUserEntity->getACL();
    }

    /**
     * Loads objects accesses
     * @param ISecurityEntity $objUserEntity
     */
    public static function loadObjectAccesses(ISecurityEntity $objUserEntity)
    {
        self::$hashObjectsAccesses = $objUserEntity->getACO();
    }

    /**
     * Indicates if the current entity has access to a specific zone in the application
     *
     * @param string $strZoneAction
     * @param string $strActionName
     * @return bool
     */
    public static function hasAccess($strZoneAction, $strActionName)
    {
        return isset(self::$hashAccesses[$strZoneAction . '-' . $strActionName]);
    }

    /**
     * Indicates if an "accessor" object is authorized to access to an "accessed" object, with specified
     *
     * @param ISecurityEntity $objAccessorObject
     * @param ISecurityEntity $objAccessedObject
     * @param integer $intLevel access level (read by default)
     * @return bool
     */
    public static function hasObjectAccess(ISecurityEntity $objAccessorObject, ISecurityEntity $objAccessedObject, $intLevel = self::PERMISSION_READ)
    {
        $strAccessedClassName = get_class($objAccessedObject);
        $strAccessorClassName = get_class($objAccessorObject);

        if (
            !array_key_exists($strAccessorClassName, self::$hashObjectsAccesses)
            || !array_key_exists($strAccessedClassName, self::$hashObjectsAccesses[$strAccessorClassName])
        ) {
            return false;
        }

        if (!is_array(self::$hashObjectsAccesses[$strAccessorClassName][$strAccessedClassName])) {
            return false;
        }

        if (!array_key_exists($objAccessedObject->getID(), self::$hashObjectsAccesses[$strAccessorClassName][$strAccessedClassName])) {
            return false;
        }

        return self::shareANDBits($intLevel, self::$hashObjectsAccesses[$strAccessorClassName][$strAccessedClassName][$objAccessedObject->getID()]);
    }

    /**
     * Indicates if an integer shares bit with another integer
     *
     * @param integer $intA
     * @param integer $intB
     * @return bool
     */
    public static function shareANDBits($intA, $intB)
    {
        return ($intA & $intB) !== 0;
    }
}

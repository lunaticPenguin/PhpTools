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

    protected static $hashAccesses = [];

    protected static $hashObjectsAccesses = [];

    protected static $hashGroups = [];

    /**
     * Current users sessions
     * @var ISecurityEntity[]
     */
    protected static $arrayUsers = [];

    /**
     * Loads standards accesses (areas)
     * @param ISecurityEntity $objUserEntity
     */
    public static function loadUser(ISecurityEntity $objUserEntity, $strType = 'user')
    {
        self::$hashAccesses[$strType] = $objUserEntity->getACL();
        self::$hashObjectsAccesses[$strType] = $objUserEntity->getACO();
        self::$hashGroups[$strType] = $objUserEntity->getGroups();
        self::$arrayUsers[$strType] = $objUserEntity;
    }

    /**
     * Returns loaded user entity
     * @return ISecurityEntity
     */
    public static function getUser($strType = 'user')
    {
        return isset(self::$arrayUsers[$strType]) ? self::$arrayUsers[$strType] : null;
    }

    /**
     * Indicates if the current entity belongs to the specified group
     * @param string $strGroupName
     * @return bool
     */
    public static function belongsToGroup($strGroupName, $strType = 'user')
    {
        return isset(self::$hashGroups[$strType]) && isset(self::$hashGroups[$strType][$strGroupName]);
    }

    /**
     * Indicates if the current entity has access to a specific zone in the application
     *
     * @param string $strZoneAction
     * @param string $strActionName
     * @return bool
     */
    public static function hasAccess($strZoneAction, $strActionName, $strType = 'user')
    {
        return isset(self::$hashAccesses[$strType])
        && isset(self::$hashAccesses[$strType][$strZoneAction . '-' . $strActionName]);
    }

    /**
     * Indicates if an "accessor" object is authorized to access to an "accessed" object, with specified
     *
     * @param ISecurityEntity $objAccessorObject
     * @param ISecurityEntity $objAccessedObject
     * @param integer $intLevel access level (read by default)
     * @return bool
     */
    public static function hasObjectAccess(ISecurityEntity $objAccessorObject, ISecurityEntity $objAccessedObject, $intLevel = self::PERMISSION_READ, $strType = 'user')
    {
        $strAccessedClassName = get_class($objAccessedObject);
        $strAccessorClassName = get_class($objAccessorObject);

        if (!isset(self::$hashObjectsAccesses[$strType])) {
            return false;
        }

        if (
            !array_key_exists($strAccessorClassName, self::$hashObjectsAccesses[$strType])
            || !array_key_exists($strAccessedClassName, self::$hashObjectsAccesses[$strType][$strAccessorClassName])
        ) {
            return false;
        }

        if (!is_array(self::$hashObjectsAccesses[$strType][$strAccessorClassName][$strAccessedClassName])) {
            return false;
        }

        if (!array_key_exists($objAccessedObject->getID(), self::$hashObjectsAccesses[$strType][$strAccessorClassName][$strAccessedClassName])) {
            return false;
        }

        return self::shareANDBits($intLevel, self::$hashObjectsAccesses[$strType][$strAccessorClassName][$strAccessedClassName][$objAccessedObject->getID()]);
    }

    /**
     * Indicates if a user is logged in.
     * Technically, this method must only be used when the Security class owns the current user
     * @return bool
     */
    public static function isLoggedIn($strType = 'user')
    {
        return self::getUser($strType) instanceof ISecurityEntity;
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

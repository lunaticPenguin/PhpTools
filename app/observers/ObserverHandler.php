<?php
namespace App\Observers;

/**
 * Class ObserverHandler
 *
 * Allows to load, register and execute observers (AbstractObserver type).
 */
class ObserverHandler
{
    /**
     * Contains all observers mapped using their names
     * @var array
     */
    protected static $hashObserversTable = array();

    /**
     * Contains all hooks of registered observers
     * @var array
     */
    protected static $hashHooksTable = array(
        'simple'    => array(), // simple means no returned data
        'modifier'  => array()  // modifier means a value can be modified and return by concerned observers
    );

    /**
     * Load all observers
     * @param array $arrayRegisteredObservers contains all observers' classes of all registered observers
     */
    public static function load(array $arrayRegisteredObservers)
    {
        foreach ($arrayRegisteredObservers as $strObserverName) {
            if (class_exists($strObserverName)) {
                $objObserver = new $strObserverName();
                if ($objObserver instanceof AbstractObserver) {
                    self::$hashObserversTable[$objObserver->getName()] = $objObserver;
                    $objObserver->load();
                }
            }
        }
    }

    /**
     * Subscribe observer's method with no returned data as "simple hook"
     *
     * @param string $strHookName
     * @param string $strObserverName
     * @param string $strMethodToCall
     *
     * @param $intPriority (facultative), allow to sort observers' methods with priority (0 => high, 100 => low)
     */
    public static function addHook($strHookName, $strObserverName, $strMethodToCall, $intPriority = 10)
    {
        self::commonAddHook('simple', $strHookName, $strObserverName, $strMethodToCall, $intPriority);
    }

    /**
     * Execute methods bound to a "simple hook" (no returned data)
     *
     * @see ObserverHandler::applyMHook()
     *
     * @param string $strHookName
     * @param array $hashParams
     */
    public static function applyHook($strHookName, array $hashParams = array())
    {
        self::commonApplyHook('simple', $strHookName, null, $hashParams);
    }

    /**
     * Subscribe observer's method with returned data as "modifier hook"
     * @param string $strHookName event's name
     * @param string $strObserverName
     * @param string $strMethodToCall
     * @param $intPriority (facultative), allow to sort observers' methods with priority (0 => high, 100 => low)
     */
    public static function addMHook($strHookName, $strObserverName, $strMethodToCall, $intPriority = 10)
    {
        self::commonAddHook('modifier', $strHookName, $strObserverName, $strMethodToCall, $intPriority);
    }

    /**
     * Execute methods bound to a "modifier hook" (returned data)
     *
     * @see ObserverHandler::applyHook()
     *
     * @param string $strHookName
     * @param array $hashParams
     * @param mixed  $mixedReturnedData les données qui sont retournées
     * @return array
     */
    public static function applyMHook($strHookName, $mixedReturnedData, array $hashParams = array())
    {
        return self::commonApplyHook('modifier', $strHookName, $mixedReturnedData, $hashParams);
    }


    /**
     * Common way to register an hook
     * @param string $strType
     * @param string $strHookName
     * @param string $strObserverName
     * @param string $strMethodToCall
     * @param integer $intPriority
     *
     * @return boolean success|failure
     */
    protected static function commonAddHook($strType, $strHookName, $strObserverName, $strMethodToCall, $intPriority = 10)
    {
        $intPriority = (int) $intPriority;

        if (!isset(self::$hashHooksTable[$strType][$strHookName])) {
            self::$hashHooksTable[$strType][$strHookName] = array();
        }

        if (!isset(self::$hashHooksTable[$strType][$strHookName][$intPriority])) {
            self::$hashHooksTable[$strType][$strHookName][$intPriority] = array();
        }

        // skip if the hook with an identical priority already exists for the same observer
        if (isset(self::$hashHooksTable[$strType][$strHookName][$intPriority])) {
            foreach (self::$hashHooksTable[$strType][$strHookName][$intPriority] as $hashHookInfos) {
                if ($hashHookInfos['observer'] === $strObserverName && $hashHookInfos['method'] === $strMethodToCall) {
                    return false;
                }
            }
        }

        self::$hashHooksTable[$strType][$strHookName][$intPriority][] = array(
            'observer' => $strObserverName,
            'method' => $strMethodToCall
        );
        ksort(self::$hashHooksTable[$strType][$strHookName]);
        return true;
    }

    /**
     * Common way to execute methods bound to an hook
     *
     * @param string $strType
     * @param string $strHookName
     * @param array $hashParams
     * @param mixed $mixedReturnedData
     *
     * @return array
     */
    protected static function commonApplyHook($strType, $strHookName, $mixedReturnedData, array $hashParams)
    {
        if (isset(self::$hashHooksTable[$strType][$strHookName])) {
            foreach (self::$hashHooksTable[$strType][$strHookName] as $arrayObservers) {
                foreach ($arrayObservers as $hashObserverInfos) {

                    if ($strType === 'simple') {
                        call_user_func(
                            array(self::$hashObserversTable[$hashObserverInfos['observer']], $hashObserverInfos['method']),
                            $hashParams
                        );
                    } else {
                        $mixedReturnedData = call_user_func(
                            array(self::$hashObserversTable[$hashObserverInfos['observer']], $hashObserverInfos['method']),
                            $mixedReturnedData,
                            $hashParams
                        );
                    }
                }
            }
        }
        return $mixedReturnedData;
    }

    /**
     * Exports all observers informations with a readable format, to help debug process
     * @return array
     */
    public static function debug()
    {
        $hashDebug = array();
        foreach (self::$hashHooksTable as $strType => $hashHooks) {
            $hashDebug[$strType] = array();
            foreach ($hashHooks as $strHook => $hashPriorities) {
                foreach ($hashPriorities as $intPriority => $hashObserverInfos) {
                    if (!isset($hashDebug[$strType][$strHook])) {
                        $hashDebug[$strType][$strHook] = array();
                    }
                    $hashDebug[$strType][$strHook][] = sprintf(
                        '%s::%s - %d',
                        strtoupper($hashObserverInfos[0]['observer']),
                        $hashObserverInfos[0]['method'],
                        $intPriority
                    );
                }
            }
        }
        return $hashDebug;
    }
}

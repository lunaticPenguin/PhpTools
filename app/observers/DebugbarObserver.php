<?php

namespace App\Observers;

use DebugBar\DataCollector\MessagesCollector;
use DebugBar\DataCollector\PDO\PDOCollector;
use DebugBar\DataCollector\PDO\TraceablePDO;
use DebugBar\StandardDebugBar;

class DebugbarObserver extends AbstractObserver
{
    /**
     * @var StandardDebugBar|null
     */
    protected $objDebugbar = null;

    protected static $arrayRegisteredCollectors = ['user'];

    public function __construct()
    {
        $this->objDebugbar = new StandardDebugBar();
        $this->objDebugbar->addCollector(new MessagesCollector('user'));
    }

    /**
     * Load hooks into system
     */
    public function load()
    {
        ObserverHandler::addMHook('runtime_declare_pdo', $this->getName(), 'add_database_collector');
        ObserverHandler::addMHook('runtime_after_action', $this->getName(), 'add_data_after_runtime');
        ObserverHandler::addHook('runtime_catch_exception', $this->getName(), 'collect_exception');
        ObserverHandler::addMHook('runtime_after_exception', $this->getName(), 'add_data_after_runtime');
    }

    /**
     * Replace original PDO instance by TraceablePDO instance
     * @param \PDO $objPDO
     * @params array $hashOptions
     * @return TraceablePDO
     * @throws \DebugBar\DebugBarException
     */
    public function add_database_collector(\PDO $objPDO, $hashOptions = [])
    {
        $objTraceablePDO = new TraceablePDO($objPDO);
        $this->objDebugbar->addCollector(new PDOCollector($objTraceablePDO, $this->objDebugbar->getCollector('time')));
        return $objTraceablePDO;
    }

    /**
     * Add debug bar data when the runtime is over
     * @param array $hashViewVariables
     * @param array $hashOptions
     * @return array
     */
    public function add_data_after_runtime(array $hashViewVariables, array $hashOptions)
    {
        $hashViewVariables['is_debugging'] = true;
        if (isset($_SESSION['user']) || isset($_SESSION['staff'])) {
            if (isset($_SESSION['user'])) {
                $this->addContent('user', $_SESSION['user'], 'info', false);
            }
            if (isset($_SESSION['staff'])) {
                $this->addContent('user', $_SESSION['staff'], 'info', false);
            }
        } else {
            $this->addContent('user', 'No user logged in.', 'info');
        }

        $objDebugBarRender = $this->objDebugbar->getJavascriptRenderer();
        ob_start();
        $objDebugBarRender->dumpCssAssets();
        $hashViewVariables['debug_bar_css'] = ob_get_contents();
        ob_clean();

        ob_start();
        $objDebugBarRender->dumpJsAssets();
        $hashViewVariables['debug_bar_js'] = ob_get_contents();
        ob_clean();
        $hashViewVariables['debug_bar_init'] = $objDebugBarRender->render();

        return $hashViewVariables;
    }

    /**
     * Add exception to the debug bar
     * @param array $hashOptions
     */
    public function collect_exception(array $hashOptions)
    {
        $this->objDebugbar['exceptions']->addException($hashOptions['e']);
    }

    /**
     * Add content to a collector in the debug bar
     * @param string $strCollectorType
     * @param mixed $mixedData
     * @param string $label
     * @param bool $isString
     * @throws \DebugBar\DebugBarException
     */
    protected function addContent($strCollectorType, $mixedData, $label = 'info', $isString = true)
    {
        if (!in_array($label, array('info', 'warn', 'error'))) {
            $label = 'info';
        }
        if (!in_array($strCollectorType, self::$arrayRegisteredCollectors)) {
            return;
        }
        $this->objDebugbar[$strCollectorType]->addMessage($mixedData, $label, $isString);
    }
}

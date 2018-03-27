<?php

namespace Dcp\Pu;
/**
 * @author Anakeen
 * @package Dcp\Pu
 */

require_once 'PU_testcase_dcp.php';

class TestCaseDcpAction extends TestCaseDcp
{
    /**
     * Action to test initiate by setUpAction
     * 
     * @var \Anakeen\Core\Internal\Action
     */
    protected $testAction;
    
    private $currentSetParameters = array();
    
    /**
     * Set up a false Action object can be used to execute action
     * 
     * @param string $appName application name
     * @param string $actionName action name
     * 
     * @return void
     */
    protected function setUpTestAction($appName, $actionName) {
        global $action;
        
        SetHttpVar("app", $appName);
        SetHttpVar("action", $actionName);
        
        $appCalled = new \Anakeen\Core\Internal\Application();
        $appCalled->set($appName, $action->parent,$action->parent->session);
        $appCalled->user=$action->user;
        if ($appCalled->isAffected() ) {
          $this->testAction = new \Anakeen\Core\Internal\Action();
          $this->testAction->set($actionName, $appCalled);
        }
    }
    
    protected function setCurrentParameters($param, $value) {
        $this->currentSetParameters[] = $param;
        setHttpVar($param, $value);
    }
    
    protected function resetCurrentParameters() {
        foreach ($this->currentSetParameters as $currentParam) {
            setHttpVar($currentParam, null);
        }
        $this->currentSetParameters = array();
    }
}
<?php
/*
 * @author Anakeen
 * @package CONTROL
*/

class WiffCommon
{
    public $errorMessage = '';
    
    public function clearErrorMessage()
    {
        $this->errorMessage = '';
    }
    
    public function setErrorMessage($msg, $pri = LOG_ERR)
    {
        $this->errorMessage = $msg;
        /*
         * Catch callstack to generate log message with error location
        */
        try {
            throw new Exception($msg);
        }
        catch(Exception $e) {
            $trace = $e->getTrace();
            $method = $trace[1]['function'];
            if (isset($trace[1]['class'])) {
                $method = sprintf("%s::%s", $method, $trace[1]['class']);
            }
            $line = $trace[0]['line'];
            $this->log($pri, sprintf("%s %s@%d %s", PHP_SAPI, $method, $line, $msg));
        }
    }
    
    public function log($pri, $msg)
    {
        require_once 'class/Class.WIFF.php';
        $wiff = WIFF::getInstance();
        $wiff->log($pri, $msg);
    }
    
    public function activity($msg)
    {
        $this->log(LOG_INFO, $msg);
    }
}

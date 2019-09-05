<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Pu;

use Anakeen\TestUnits\CoreTests;

/**
 * @author Anakeen
 * @package Dcp\Pu
 */
//require_once 'PHPUnit/Framework.php';



set_include_path(get_include_path() . PATH_SEPARATOR . "./DCPTEST:./WHAT");

// ...
class TestSuiteWorkflow
{
    const logFile = CoreTests::LOGFILE;
    const msgFile = CoreTests::MSGFILE;

    public static $allInProgress = false;
    public static function suite()
    {
        self::configure();
        self::$allInProgress = true;
        $suite = new FrameworkDcp('Project');
        
        $suite->addTest(SuiteWorkflow::suite());
        // ...
        printf("\nerror log in %s, messages in %s\n", self::logFile, self::msgFile);
        return $suite;
    }
    
    public static function configure()
    {
        @unlink(self::logFile);
        ini_set("error_log", self::logFile);
        file_put_contents(self::msgFile, strftime('%Y-%m-%d %T'));
    }
    
    public static function addMessage($msg)
    {
        
        if (!self::$allInProgress) {
            print "$msg\n";
        } else {
            file_put_contents(self::msgFile, $msg, FILE_APPEND);
        }
    }
}

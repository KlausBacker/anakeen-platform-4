<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Pu;
/**
 * @author Anakeen
 * @package Dcp\Pu
 */
//require_once 'PHPUnit/Framework.php';


require_once __DIR__."/../WHAT/Lib.Prefix.php";

set_include_path(get_include_path() . PATH_SEPARATOR . "./DCPTEST:./WHAT");

require_once 'WHAT/autoload.php';
// ...
class TestSuiteWorkflow
{
    const logFile = "/var/tmp/pudcp.log";
    const msgFile = "/var/tmp/pudcp.msg";
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
?>

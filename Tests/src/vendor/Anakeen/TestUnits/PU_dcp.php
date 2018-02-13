<?php
/*
 * @author Anakeen
 * @package Dcp\Pu
*/

namespace Dcp\Pu;

use Anakeen\Pu\Routes\SuiteRoutes;

require_once __DIR__ . "/../WHAT/Lib.Prefix.php";

set_include_path(get_include_path() . PATH_SEPARATOR . "./DCPTEST:./WHAT");

require_once 'WHAT/autoload.php';

// ...
class TestSuiteDcp
{
    const logFile = "/var/tmp/pudcp.log";
    const msgFile = "/var/tmp/pudcp.msg";
    public static $allInProgress = false;

    public static function suite()
    {
        self::configure();
        self::$allInProgress = true;
        $suite = new FrameworkDcp('Project');

        $suite->addTest((new SuiteRoutes)());
        $suite->addTest(SuiteDcp::suite());
        $suite->addTest(SuiteDcpAttribute::suite());
        $suite->addTest(SuiteDcpUser::suite());
        $suite->addTest(SuiteDcpSecurity::suite());
        $suite->addTest(SuiteApi::suite());
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

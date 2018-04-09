<?php
/*
 * @author Anakeen
 * @package Dcp\Pu
*/

namespace Anakeen\TestUnits;

use Anakeen\Core\Utils\Gettext;
use Dcp\Pu\FrameworkDcp;
use Dcp\Pu\SuiteWorkflow;

require __DIR__ . '/../autoload.php';
require __DIR__ . '/../WHAT/Lib.Prefix.php';

set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__);


// ...
class WorkflowTests
{
    const LOGFILE = "/var/tmp/pudcp.log";
    const MSGFILE = "/var/tmp/pudcp.msg";

    public static $allInProgress = false;

    public static function suite()
    {
        self::configure();
        self::$allInProgress = true;
        $suite = new FrameworkDcp('Workflow');

        Gettext::___("Hello"); // Include ___
           $suite->addTest(SuiteWorkflow::suite());
        // ...
        printf("\nError log in [%s], messages in [%s]\n", self::LOGFILE, self::MSGFILE);
        return $suite;
    }

    public static function configure()
    {
        @unlink(self::LOGFILE);
        ini_set("error_log", self::LOGFILE);
        file_put_contents(self::MSGFILE, strftime('%Y-%m-%d %T'));
    }

    public static function addMessage($msg)
    {

        if (!self::$allInProgress) {
            print "$msg\n";
        } else {
            file_put_contents(self::MSGFILE, $msg, FILE_APPEND);
        }
    }
}

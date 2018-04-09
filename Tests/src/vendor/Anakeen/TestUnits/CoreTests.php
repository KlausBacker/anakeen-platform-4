<?php
/*
 * @author Anakeen
 * @package Dcp\Pu
*/

namespace Anakeen\TestUnits;

use Anakeen\Core\Utils\Gettext;
use Anakeen\Pu\Routes\SuiteRoutes;
use Dcp\Pu\FrameworkDcp;
use Dcp\Pu\SuiteDcp;
use Dcp\Pu\SuiteDcpAttribute;
use Dcp\Pu\SuiteDcpSecurity;
use Dcp\Pu\SuiteDcpUser;

require_once __DIR__ . '/../autoload.php';
require_once __DIR__ . '/../WHAT/Lib.Prefix.php';

set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__);


// ...
class CoreTests
{
    const LOGFILE = "/var/tmp/pudcp.log";
    const MSGFILE = "/var/tmp/pudcp.msg";

    public static $allInProgress = false;

    public static function suite()
    {
        self::configure();
        self::$allInProgress = true;
        $suite = new FrameworkDcp('Project');

        Gettext::___("Hello"); // Include ___
     //   $suite->addTest((new SuiteRoutes)());
     //   $suite->addTest(SuiteDcp::suite());
      //  $suite->addTest(SuiteDcpAttribute::suite());
      //  $suite->addTest(SuiteDcpUser::suite());
        $suite->addTest(SuiteDcpSecurity::suite());
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

<?php
/*
 * @author Anakeen
 * @package Dcp\Pu
*/

namespace Anakeen\TestUnits;

use Anakeen\Core\Utils\Gettext;
use Dcp\Pu\FrameworkDcp;
use Dcp\Pu\SuiteWorkflow;


set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__);


// ...
class WorkflowTests
{
    const LOGFILE = "/var/tmp/pudcp.log";
    const MSGFILE = "/var/tmp/pudcp.msg";

    public static $allInProgress = false;

    public static function suite()
    {
        $suite = new FrameworkDcp('Workflow');

        Gettext::___("Hello"); // Include ___
           $suite->addTest(SuiteWorkflow::suite());
        // ...

        return $suite;
    }

}

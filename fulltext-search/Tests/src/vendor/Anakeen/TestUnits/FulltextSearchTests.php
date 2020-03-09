<?php
/*
 * @author Anakeen
 * @package Dcp\Pu
*/

namespace Anakeen\TestUnits;

use Anakeen\Core\Utils\Gettext;
use Anakeen\Pu\FulltextSearch\SuiteFulltextSearchConfig;
use Dcp\Pu\FrameworkDcp;

set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__);


// ...
class FulltextSearchTests
{
    public static $allInProgress = false;

    public static function suite()
    {
        $suite = new FrameworkDcp('Workflow');

        Gettext::___("Hello"); // Include ___
        $suite->addTest(SuiteFulltextSearchConfig::suite());
        // ...

        return $suite;
    }
}

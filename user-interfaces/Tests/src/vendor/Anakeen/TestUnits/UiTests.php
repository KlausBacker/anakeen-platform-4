<?php

namespace Anakeen\TestUnits;

use Anakeen\Core\Utils\Gettext;
use Anakeen\Pu\SuiteUi;
use Dcp\Pu\FrameworkDcp;

set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__);


class UiTests
{
    public static $allInProgress = false;

    public static function suite()
    {
        $suite = new FrameworkDcp('Ui');

        Gettext::___("Hello"); // Include ___
        $suite->addTest(SuiteUi::suite());


        return $suite;
    }
}

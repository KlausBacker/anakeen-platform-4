<?php

namespace Anakeen\Pu\Config;

use Dcp\Pu\FrameworkDcp;

class SuiteWorkflowConfig
{
    public static function suite()
    {
        $r=new SuiteWorkflowConfig();
        return $r();
    }

    public function __invoke()
    {
        $suite = new FrameworkDcp();

        $suite->addTestSuite(\Anakeen\Pu\Config\PuStructWorkflow::class);
        $suite->addTestSuite(\Anakeen\Pu\Config\PuTimer::class);

        return $suite;
    }
}

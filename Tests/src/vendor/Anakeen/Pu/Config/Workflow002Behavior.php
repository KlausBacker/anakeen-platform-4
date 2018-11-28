<?php

namespace Anakeen\Pu\Config;

class Workflow002Behavior extends \SmartStructure\Wdoc
{
    public function __construct($dbaccess = '', $id = '', $res = '', $dbid = 0)
    {
        $this->useWorkflowGraph(__DIR__."/Inputs/tst_W002.graph.xml");
        parent::__construct($dbaccess, $id, $res, $dbid);
    }
}

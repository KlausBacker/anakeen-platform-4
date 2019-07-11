<?php

namespace Anakeen\SmartStructures\Devbill\Workflows;

/**
 * Workflow Behavior of WFAM_BILL elements
 */
class WFamDevBillBehavior extends \SmartStructure\Wdoc
{
    public function __construct($dbaccess = '', $id = '', $res = '', $dbid = 0)
    {
        $this->useWorkflowGraph(__DIR__."/Wfam_billGraph.xml");
        parent::__construct($dbaccess, $id, $res, $dbid);

        // @FIXME Insert Here transition coniguration
    }
}
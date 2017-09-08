<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Pu;

require_once 'PU_testcase_dcp.php';

class TestHelpUsage extends TestCaseDcp
{
    /**
     * @dataProvider dataTextHelpUsage
     * @param string $api
     */
    public function testTextHelpUsage($api)
    {
        $output = array();
        exec(getWshCmd() . " --api=" . $api . " --help 2> /dev/null", $output);
        $this->assertTrue($output[1] == "Usage :", "String usage not found for api " . $api);
    }
    
    public function dataTextHelpUsage()
    {
        return array(
            array(
                "manageApplications"
            ) ,
            array(
                "manageContextCrontab"
            ) ,
            array(
                "generateDocumentClass"
            ) ,
            array(
                "processExecute"
            ) ,
            array(
                "destroyFamily"
            ) ,
            array(
                "fdl_trigger"
            ) ,
            array(
                "cleanContext"
            ) ,
            array(
                "getApplicationParameter"
            ) ,
            array(
                "importDocuments"
            ) ,
            array(
                "setStyle"
            ) ,
            array(
                "ods2csv"
            ) ,
            array(
                "refreshDocuments"
            ) ,
            array(
                "refreshjsversion"
            ) ,
            array(
                "setApplicationParameter"
            ) ,
            array(
                "updateclass"
            ) ,
            array(
                "vault_init"
            )
        );
    }
}

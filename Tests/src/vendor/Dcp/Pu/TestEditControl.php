<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Pu;

use Anakeen\Core\DbManager;
use Anakeen\Core\SEManager;
use Anakeen\SmartElementManager;

/**
 * @author  Anakeen
 * @package Dcp\Pu
 */
//require_once 'PU_testcase_dcp_commonfamily.php';

class TestEditControl extends TestCaseDcpCommonFamily
{
    protected static function getCommonImportFile()
    {
        return array(
            "PU_data_dcp_editcontrol.ods"
        );
    }

    /**
     * Test withoutControl=true is retained after revise/modify
     *
     * @param $data
     * @throws \Dcp\Exception
     *
     * @dataProvider dataEditControlAfterReviseModify
     */
    public function testEditControlAfterReviseModify($data)
    {
        $this->sudo($data['login']);
        $doc = SEManager::getDocument($data['doc']);
        $err = $doc->revise();
        $this->assertEmpty($err, sprintf("revise() returned an unexpected error on document '%s' with user '%s': %s", $data['doc'], $data['login'], $err));
        $err = $doc->modify();
        $this->assertEmpty($err, sprintf("modify() returned an unexpected error on document '%s' with user '%s': %s", $data['doc'], $data['login'], $err));
        $this->exitSudo();
    }

    public function dataEditControlAfterReviseModify()
    {
        return array(
            array(
                array(
                    'login' => 'u_editcontrol',
                    'doc' => 'TST_EDITCONTROL_1'
                )
            )
        );
    }

    /**
     * Test revise() is forbidden without disableAccessControl()
     *
     * @param $data
     *
     * @dataProvider dataRevise
     */
    public function testRevise($data)
    {
        $this->sudo($data['login']);
        $doc = SmartElementManager::getDocument($data['doc']);
        $docId = $doc->id;
        $err = $doc->revise();
        $revs = $this->_revs($docId);
        $this->assertNotEmpty($err, sprintf("revise() did not returned an error on document '%s' with user '%s'.", $data['doc'], $data['login']));
        $this->assertTrue((count($revs) <= 0), sprintf("Document '%s' has been revised in database: %s", $data['doc'], var_export($revs, true)));
        $this->exitSudo();
    }

    public function dataRevise()
    {
        return array(
            array(
                array(
                    'login' => 'u_editcontrol',
                    'doc' => 'TST_EDITCONTROL_1'
                )
            )
        );
    }

    public function _revs($docId)
    {
        $q = sprintf("SELECT revs.id FROM doc, doc AS revs WHERE doc.id = '%s' AND doc.initid = revs.initid AND revs.id > doc.id ORDER BY revs.id", pg_escape_string($docId));
        DbManager::query($q, $res);
        return $res;
    }
}

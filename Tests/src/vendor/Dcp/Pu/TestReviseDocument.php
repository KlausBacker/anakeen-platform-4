<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Pu;
/**
 * @author Anakeen
 * @package Dcp\Pu
 */

use Anakeen\Core\ContextManager;
use Anakeen\Core\DocManager;

//require_once 'PU_testcase_dcp_commonfamily.php';

class TestReviseDocument extends TestCaseDcpCommonFamily
{

    private static $ids = array();

    public static function getCommonImportFile()
    {
        ContextManager::setLanguage("fr_FR");
        return "PU_data_dcp_revise.csv";
    }

    /**
     * @dataProvider dataRevise
     */
    public function testRevision($docName, $revisionsNumber, $expectRevisionNumber, $expectedTitles)
    {
        $d = DocManager::getDocument($docName);
        $d->setValue("TST_REV", $d->revision);
        for ($i=0; $i<$revisionsNumber - 1; $i++) {
            $err=$d->revise();
            $d->setValue("TST_REV", $d->revision);
            $this->assertEmpty($err, "Error : $err");
        }
        $d->store();

        $revisions=$d->getRevisions("TABLE");
        $this->assertEquals($expectRevisionNumber, count($revisions));

        foreach ($expectedTitles as $revisionNumber=>$title) {
            $id=DocManager::getRevisedDocumentId($d->initid, $revisionNumber);
            $rev=DocManager::getDocument($id, false);
            $this->assertNotEmpty( $rev, "Revision {$d->initid}/$id/$revisionNumber");
            $this->assertEquals( $title, $rev->getTitle());
        }

    }

    public function dataRevise()
    {
        return array(
            array(
                "TST_REVISE1",
                10,10,
                [0=>"Cornichon 0", 1=>"Cornichon 1", 9=>"Cornichon 9"]

            ),
            array(
                "TST_REVISE2",
                10,5,
                [6=>"Tomate 6", 7=>"Tomate 7", 9=>"Tomate 9"]

            )

        );
    }

}

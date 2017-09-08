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

require_once 'PU_testcase_dcp_commonfamily.php';

class TestImportXmlDocuments extends TestCaseDcpCommonFamily
{
    /**
     * import TST_FAMIMP1 family
     * @static
     * @return string
     */
    protected static function getCommonImportFile()
    {
        return "PU_data_dcp_importfamily1.ods";
    }
    /**
     * count error from import report message
     * @param $err
     * @return int|void
     */
    private function countErrors($err)
    {
        if (!$err) return 0;
        return count(explode("]\n[", $err));
    }
    /**
     * @dataProvider dataDocumentFiles
     * @param string $documentFile
     * @param int $nbError
     * @throws \Dcp\Db\Exception
     */
    public function testErrorImportDocument($documentFile, $nbError)
    {
        $err = '';
        try {
            $this->importDocument($documentFile);
        }
        catch(\Dcp\Exception $e) {
            $err = $e->getMessage();
        }
        $this->assertNotEmpty($err);
        $this->assertEquals($nbError, $this->countErrors($err) , sprintf('status error : "%s"', $err));
        
        $s = new \SearchDoc("", "TST_FAMIMP1");
        $this->assertEquals(0, $s->onlyCount() , "document is created and must be not");
    }
    /**
     * @dataProvider dataIuserFiles
     * @param string $documentFile
     */
    public function testErrorImportIuser($documentFile)
    {
        $err = '';
        try {
            $this->importDocument($documentFile);
        }
        catch(\Dcp\Exception $e) {
            $err = $e->getMessage();
        }
        $this->assertNotEmpty($err);
    }
    /**
     * @dataProvider dataFolderImportDocument
     * @param string $documentFile
     * @param string $docName
     * @param array $folderNames
     */
    public function testFolderImportDocument($documentFile, $docName, array $folderNames)
    {
        $err = '';
        try {
            $this->importDocument($documentFile);
        }
        catch(\Dcp\Exception $e) {
            $err = $e->getMessage();
        }
        $this->assertEmpty($err, sprintf("Error : $err"));
        $doc = new_doc("", $docName);
        $this->assertTrue($doc->isAlive() , sprintf("cannot import %s document", $docName));
        $folders = $doc->getParentFolderIds();
        foreach ($folderNames as $folder) {
            $fid = getIdFromName(self::$dbaccess, $folder);
            $this->assertTrue(in_array($fid, $folders) , sprintf("folder %s not found in %s", $folder, print_r($folders, true)));
        }
    }
    /**
     * @dataProvider dataExtraImportDocument
     * @param string $documentFile
     * @param string $docName
     * @param array $extraValues
     */
    public function testExtraImportDocument($documentFile, $docName, array $extraValues)
    {
        $err = '';
        try {
            $this->importDocument($documentFile);
        }
        catch(\Dcp\Exception $e) {
            $err = $e->getMessage();
        }
        $this->assertEmpty($err, sprintf("Error : $err"));
        $doc = new_doc("", $docName);
        $this->assertTrue($doc->isAlive() , sprintf("cannot import %s document", $docName));
        $tColK = $doc->getMultipleRawValues("tst_extrakey");
        $tColv = $doc->getMultipleRawValues("tst_extraval");
        $tExtra = array();
        foreach ($tColK as $k => $v) {
            $tExtra[$v] = $tColv[$k];
        }
        foreach ($extraValues as $expKey => $expVal) {
            
            $this->assertEquals($expVal, $tExtra[$expKey], sprintf("not correct extra : %s", print_r($tExtra, true)));
        }
    }
    
    public function dataDocumentFiles()
    {
        return array(
            array(
                "PU_data_dcp_importdoc1.xml",
                1
            ) ,
            array(
                "PU_data_dcp_importdoc2.xml",
                1
            ) ,
            array(
                "PU_data_dcp_importdoc3.xml",
                2
            ) ,
            array(
                "PU_data_dcp_importdoc4.xml",
                1
            )
        );
    }
    public function dataIuserFiles()
    {
        return array(
            array(
                "PU_dcp_data_iuser.xml"
            )
        );
    }
    
    public function dataExtraImportDocument()
    {
        return array(
            array(
                "PU_data_dcp_importdoc8.xml",
                "TST_DOCIMP4",
                array(
                    'state' => "test",
                    'title' => "Un",
                    "revision" => 2
                )
            )
        );
    }
    public function dataFolderImportDocument()
    {
        return array(
            array(
                "PU_data_dcp_importdoc5.xml",
                "TST_DOCIMP1",
                array(
                    'TST_FOLDER1'
                )
            ) ,
            array(
                "PU_data_dcp_importdoc6.xml",
                "TST_DOCIMP2",
                array(
                    'TST_FOLDER1',
                    'TST_FOLDER2'
                )
            ) ,
            array(
                "PU_data_dcp_importdoc7.xml",
                "TST_DOCIMP3",
                array(
                    'TST_FOLDER1',
                    'TST_FOLDER2',
                    'TST_FOLDER3'
                )
            )
        );
    }
}
?>
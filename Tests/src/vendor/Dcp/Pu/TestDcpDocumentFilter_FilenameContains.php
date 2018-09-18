<?php

namespace Dcp\Pu;


use Anakeen\Core\SEManager;

class TestDcpDocumentFilter_FilenameContains extends TestDcpDocumentFilter_common
{
    const FAM = 'TEST_DCP_DOCUMENTFILTER_FILENAMECONTAINS';
    protected static function getCommonImportFile()
    {
        return array(
            "PU_data_dcp_documentFilter_FilenameContains.ods"
        );
    }
    protected function setUp()
    {
        parent::setUp();
        $this->localSetup();
    }
    public function localSetup()
    {
        $f = array(
            0 => 'application/binary|10|Les chevaux.doc',
            1 => 'application/binary|11|Les moutons.doc',
            2 => 'application/binary|12|Les chevaux.doc',
            3 => 'application/binary|13|Les moutons.doc',
            4 => 'application/binary|14|Le cheval.pdf'
        );
        $doc = SEManager::getDocument('FILENAMECONTAINS_1');
        $doc->setAttributeValue('S_FILE', '');
        $doc->setAttributeValue('S_IMAGE', '');
        $doc->setAttributeValue('A_FILE', array());
        $doc->setAttributeValue('A_IMAGE', array());
        $doc->modify();
        unset($doc);
        $doc = SEManager::getDocument('FILENAMECONTAINS_2');
        $doc->setAttributeValue('S_FILE', $f[0]);
        $doc->setAttributeValue('S_IMAGE', $f[0]);
        $doc->setAttributeValue('A_FILE', array(
            $f[2],
            $f[3]
        ));
        $doc->setAttributeValue('A_IMAGE', array(
            $f[2],
            $f[3]
        ));
        $doc->modify();
        unset($doc);
        $doc = SEManager::getDocument('FILENAMECONTAINS_3');
        $doc->setAttributeValue('S_FILE', $f[1]);
        $doc->setAttributeValue('S_IMAGE', $f[1]);
        $doc->setAttributeValue('A_FILE', array(
            $f[2],
            $f[3],
            $f[4]
        ));
        $doc->setAttributeValue('A_IMAGE', array(
            $f[2],
            $f[3],
            $f[4]
        ));
        $doc->modify();
        unset($doc);
    }
    /**
     * @param $test
     * @dataProvider data_FilenameContains
     */
    public function test_FilenameContains($test)
    {
        if (is_a($test["value"], LateNameResolver::class)) {
            $test["value"] = $test["value"]->value;
        }
        $this->common_testFilter($test["fam"], new \Anakeen\Search\Filters\FilenameContains($test["attr"], $test["value"], (isset($test["flags"]) ? $test["flags"] : 0)) , $test["expected"]);
    }
    
    public function data_FilenameContains()
    {
        return array(
            // S_FILE
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "S_FILE",
                    "value" => "cheva",
                    "expected" => array(
                        "FILENAMECONTAINS_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "S_FILE",
                    "value" => "cheva",
                    "flags" => \Anakeen\Search\Filters\FilenameContains::NOT,
                    "expected" => array(
                        "FILENAMECONTAINS_1",
                        "FILENAMECONTAINS_3"
                    )
                )
            ) ,
            // S_IMAGE
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "S_IMAGE",
                    "value" => "cheva",
                    "expected" => array(
                        "FILENAMECONTAINS_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "S_IMAGE",
                    "value" => "cheva",
                    "flags" => \Anakeen\Search\Filters\FilenameContains::NOT,
                    "expected" => array(
                        "FILENAMECONTAINS_1",
                        "FILENAMECONTAINS_3"
                    )
                )
            ) ,
            // A_FILE
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_FILE",
                    "value" => "cheva",
                    "expected" => array(
                        "FILENAMECONTAINS_2",
                        "FILENAMECONTAINS_3"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_FILE",
                    "value" => "cheva",
                    "flags" => \Anakeen\Search\Filters\FilenameContains::NOT,
                    "expected" => array(
                        "FILENAMECONTAINS_1"
                    )
                )
            ) ,
            // A_IMAGE
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_IMAGE",
                    "value" => "cheva",
                    "expected" => array(
                        "FILENAMECONTAINS_2",
                        "FILENAMECONTAINS_3"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_IMAGE",
                    "value" => "cheva",
                    "flags" => \Anakeen\Search\Filters\FilenameContains::NOT,
                    "expected" => array(
                        "FILENAMECONTAINS_1"
                    )
                )
            )
        );
    }
    /**
     * @param $test
     * @dataProvider data_FilenameEquals
     */
    public function test_FilenameEquals($test)
    {
        if (is_a($test["value"], LateNameResolver::class)) {
            $test["value"] = $test["value"]->value;
        }
        $this->common_testFilter($test["fam"], new \Anakeen\Search\Filters\FilenameEquals($test["attr"], $test["value"], (isset($test["flags"]) ? $test["flags"] : 0)) , $test["expected"]);
    }

    public function data_FilenameEquals()
    {
        return array(
            // S_FILE
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "S_FILE",
                    "value" => "Les chevaux.doc",
                    "expected" => array(
                        "FILENAMECONTAINS_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "S_FILE",
                    "value" => "Les chevaux.doc",
                    "flags" => \Anakeen\Search\Filters\FilenameContains::NOT,
                    "expected" => array(
                        "FILENAMECONTAINS_1",
                        "FILENAMECONTAINS_3"
                    )
                )
            ) ,
            // S_IMAGE
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "S_IMAGE",
                    "value" => "Les chevaux.doc",
                    "expected" => array(
                        "FILENAMECONTAINS_2"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "S_IMAGE",
                    "value" => "Les chevaux.doc",
                    "flags" => \Anakeen\Search\Filters\FilenameContains::NOT,
                    "expected" => array(
                        "FILENAMECONTAINS_1",
                        "FILENAMECONTAINS_3"
                    )
                )
            ) ,
            // A_FILE
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_FILE",
                    "value" => "Les chevaux.doc",
                    "expected" => array(
                        "FILENAMECONTAINS_2",
                        "FILENAMECONTAINS_3"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_FILE",
                    "value" => "Les chevaux.doc",
                    "flags" => \Anakeen\Search\Filters\FilenameContains::NOT,
                    "expected" => array(
                        "FILENAMECONTAINS_1"
                    )
                )
            ) ,
            // A_IMAGE
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_IMAGE",
                    "value" => "Les chevaux.doc",
                    "expected" => array(
                        "FILENAMECONTAINS_2",
                        "FILENAMECONTAINS_3"
                    )
                )
            ) ,
            array(
                array(
                    "fam" => self::FAM,
                    "attr" => "A_IMAGE",
                    "value" => "Les chevaux.doc",
                    "flags" => \Anakeen\Search\Filters\FilenameContains::NOT,
                    "expected" => array(
                        "FILENAMECONTAINS_1"
                    )
                )
            )
        );
    }
}

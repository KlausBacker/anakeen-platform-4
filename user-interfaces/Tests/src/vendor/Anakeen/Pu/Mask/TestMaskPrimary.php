<?php

namespace Anakeen\Pu\Mask;

use Anakeen\Core\SEManager;
use Anakeen\Pu\TestCaseUi;
use Anakeen\Routes\Ui\DocumentView;

class TestMaskPrimary extends TestCaseUi
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::importConfigurationFile(__DIR__ . "/Inputs/testmask.xml");
        self::importConfigurationFile(__DIR__ . "/Inputs/testmaskUi.xml");
    }

    /**
     * apply mask
     * @dataProvider dataPrimary
     * @param       $viewId
     * @param array $expectedVisibilities
     * @throws \Anakeen\Core\DocManager\Exception
     */
    public function testPrimary($structureName, $viewId, array $expectedVisibilities)
    {
        $elt = SEManager::createDocument($structureName);
        $this->assertNotEmpty($elt, "Cannot create $structureName element");

        $view = new DocumentViewTesting();
        $view->setUiParameter($elt, $viewId);
        $vis = $view->getVisibilities();
        foreach ($expectedVisibilities as $fieldId => $expectedVisibility) {
            $this->assertEquals($expectedVisibility, $vis[$fieldId], sprintf("Field \"%s\" : %s <> %s", $fieldId, $expectedVisibility, $vis[$fieldId]));
        }
    }

    public function dataPrimary()
    {
        return [
            [
                "TST_PMASK",
                "viewid" => DocumentView::defaultViewConsultationId,
                "vis" => [
                    "tst_fr_rw" => "W",
                    "tst_rw_rw" => "W",
                    "tst_rw_r" => "R",
                    "tst_rw_w" => "O",
                    "tst_rw_n" => "H",

                    "tst_fr_r" => "R",
                    "tst_r_rw" => "R",
                    "tst_r_r" => "R",
                    "tst_r_w" => "H",
                    "tst_r_n" => "H",

                    "tst_fr_n" => "H",
                    "tst_n_rw" => "H",
                    "tst_n_r" => "H",
                    "tst_n_w" => "H",
                    "tst_n_n" => "H",

                    "tst_fr_w" => "O",
                    "tst_w_rw" => "O",
                    "tst_w_r" => "H",
                    "tst_w_w" => "O",
                    "tst_w_n" => "H",

                    "tst_tab_n" => "H",
                    "tst_fr2_r" => "H",
                    "tst_n_r_r" => "H",
                    "tst_n_r_n" => "H",
                    "tst_n_r_rw" => "H",
                    "tst_n_r_w" => "H",

                    "tst_fr3_n" => "H",
                    "tst_n_n_r" => "H",
                    "tst_n_n_n" => "H",
                    "tst_n_n_rw" => "H",
                    "tst_n_n_w" => "H",
                ]
            ],

            [
                "TST_PMASK",
                "viewid" => "V1",
                "vis" => [
                    "tst_fr_rw" => "W",
                    "tst_rw_rw" => "W",
                    "tst_rw_r" => "R",
                    "tst_rw_w" => "O",
                    "tst_rw_n" => "H",

                    "tst_fr_r" => "R",
                    "tst_r_rw" => "H",
                    "tst_r_r" => "H",
                    "tst_r_w" => "H",
                    "tst_r_n" => "H",

                    "tst_fr_n" => "H",
                    "tst_n_rw" => "H",
                    "tst_n_r" => "H",
                    "tst_n_w" => "H",
                    "tst_n_n" => "H",

                    "tst_fr_w" => "H",
                    "tst_w_rw" => "H",
                    "tst_w_r" => "H",
                    "tst_w_w" => "H",
                    "tst_w_n" => "H",

                    "tst_tab_n" => "H",
                    "tst_fr2_r" => "H",
                    "tst_n_r_r" => "H",
                    "tst_n_r_n" => "H",
                    "tst_n_r_rw" => "H",
                    "tst_n_r_w" => "H",

                    "tst_fr3_n" => "H",
                    "tst_n_n_r" => "H",
                    "tst_n_n_n" => "H",
                    "tst_n_n_rw" => "H",
                    "tst_n_n_w" => "H",
                ]
            ],

            [
                "TST_PMASKBIS",
                "viewid" => DocumentView::defaultViewConsultationId,
                "vis" => [
                    "tst_fr_rw" => "R",
                    "tst_rw_rw" => "R",
                    "tst_rw_r" => "R",
                    "tst_rw_w" => "H",
                    "tst_rw_n" => "H",

                    "tst_fr_r" => "R",
                    "tst_r_rw" => "R",
                    "tst_r_r" => "R",
                    "tst_r_w" => "H",
                    "tst_r_n" => "H",

                    "tst_fr_n" => "H",
                    "tst_n_rw" => "H",
                    "tst_n_r" => "H",
                    "tst_n_w" => "H",
                    "tst_n_n" => "H",

                    "tst_fr_w" => "O",
                    "tst_w_rw" => "O",
                    "tst_w_r" => "H",
                    "tst_w_w" => "O",
                    "tst_w_n" => "H",

                    "tst_tab_n" => "H",
                    "tst_fr2_r" => "H",
                    "tst_n_r_r" => "H",
                    "tst_n_r_n" => "H",
                    "tst_n_r_rw" => "H",
                    "tst_n_r_w" => "H",

                    "tst_fr3_n" => "H",
                    "tst_n_n_r" => "H",
                    "tst_n_n_n" => "H",
                    "tst_n_n_rw" => "H",
                    "tst_n_n_w" => "H",
                ]
            ],

            [
                "TST_PMASKBIS",
                "viewid" => "V1",
                "vis" => [
                    "tst_fr_rw" => "R",
                    "tst_rw_rw" => "R",
                    "tst_rw_r" => "R",
                    "tst_rw_w" => "H",
                    "tst_rw_n" => "H",

                    "tst_fr_r" => "R",
                    "tst_r_rw" => "H",
                    "tst_r_r" => "H",
                    "tst_r_w" => "H",
                    "tst_r_n" => "H",

                    "tst_fr_n" => "H",
                    "tst_n_rw" => "H",
                    "tst_n_r" => "H",
                    "tst_n_w" => "H",
                    "tst_n_n" => "H",

                    "tst_fr_w" => "H",
                    "tst_w_rw" => "H",
                    "tst_w_r" => "H",
                    "tst_w_w" => "H",
                    "tst_w_n" => "H",

                    "tst_tab_n" => "H",
                    "tst_fr2_r" => "H",
                    "tst_n_r_r" => "H",
                    "tst_n_r_n" => "H",
                    "tst_n_r_rw" => "H",
                    "tst_n_r_w" => "H",

                    "tst_fr3_n" => "H",
                    "tst_n_n_r" => "H",
                    "tst_n_n_n" => "H",
                    "tst_n_n_rw" => "H",
                    "tst_n_n_w" => "H",
                ]
            ]
        ];
    }
}

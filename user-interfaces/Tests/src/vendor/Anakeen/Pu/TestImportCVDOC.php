<?php
namespace Anakeen\Pu;

class TestImportCVDOC extends \Dcp\Pu\TestCaseDcpCommonFamily
{
    /**
     * @dataProvider dataBadCVDOC
     */
    public function testErrorImportCVDOC($familyFile, array $expectedErrors)
    {
        $this->requiresCoreParamEquals('CORE_LANG', 'fr_FR');

        $err = '';
        try {
            $this->importDocument($familyFile);
        } catch (\Exception $e) {
            $err = $e->getMessage();
        }
        $this->assertNotEmpty($err, "no import error detected");
        if (!is_array($expectedErrors)) {
            $expectedErrors = array(
                $expectedErrors
            );
        }

        foreach ($expectedErrors as $expectedError) {
            $this->assertStringContainsString($expectedError, $err, sprintf("not the correct error reporting : %s", $err));
        }
    }

    public function dataBadCVDOC()
    {
        return array(
            array(
                "file" => "PU_data_dcp_importcvdocbad1.csv",
                "errors" => array(
                    "DOC0111",
                    "[Identifiant de la vue]",
                    "DOC0111",
                    "[Label]"
                ),
            )
        );
    }
}

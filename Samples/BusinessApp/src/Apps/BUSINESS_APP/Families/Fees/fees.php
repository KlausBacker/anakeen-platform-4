<?php


namespace Sample\BusinessApp;

use Dcp\AttributeIdentifiers\BA_FEES as FeesAttr;

class Fees extends \Dcp\Family\Document
{

    public function getCustomTitle()
    {
        $period = $this->getAttributeValue(FeesAttr::fee_period);
        return sprintf("Note de frais %s", $period);
    }

    public function postStore()
    {
        require_once("FDL/Lib.Vault.php");
        $outfile = getTmpDir().'/fee-preview.pdf';
        $total = 0.0;
        $advance = $this->getRawValue(FeesAttr::fee_advance);
//        $allTaxedValues = $this->getRawValue(FeesAttr::fee_exp_tax);
//        foreach ($allTaxedValues as $val) {
//            $total += $val;
//        }
        $infile = $this->viewDoc($layout = "THIS:FEE_PREVIEW_TEMPLATE:B","ooo");
        convertFile($infile, "pdf", $outfile);
        $this->setFile("fee_pdffile", $outfile);
        return "";
    }
}

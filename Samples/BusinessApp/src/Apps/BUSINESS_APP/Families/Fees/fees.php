<?php


namespace Sample\BusinessApp;

use Dcp\AttributeIdentifiers\BA_FEES as FeesAttr;

class Fees extends \Dcp\Family\Document
{

    public function getCustomTitle()
    {
        $period = $this->getAttributeValue(FeesAttr::fee_period);
        return sprintf("Note de frais %s", strftime("%B %Y", $period->getTimestamp()));
    }

    public function postStore()
    {
        require_once("FDL/Lib.Vault.php");
        $outfile = getTmpDir().'/fee-preview.pdf';
        $infile = $this->viewDoc($layout = "THIS:FEE_PREVIEW_TEMPLATE:B","ooo");
        convertFile($infile, "pdf", $outfile);
        $this->setFile("fee_pdffile", $outfile);
        return "";
    }

    /**
     * @param $target
     * @param $ulink
     * @param $abstract
     * @templateController
     */
    public function fee_preview_template($target, $ulink, $abstract) {
        $this->viewdefaultcard($target, $ulink, $abstract);
        $total = 0.0;
        $advance = $this->getRawValue(FeesAttr::fee_advance);
        $allTaxedValues = $this->getAttributeValue(FeesAttr::fee_exp_tax);
        foreach ($allTaxedValues as $val) {
            $total += $val;
        }
        $this->lay->eSet('FEE_TOTAL', $total);
        $this->lay->eSet('FEE_REPAY', $total - $advance);
    }

    public function getImagePosition($img, $position) {
        $path = $this->vault_filename_fromvalue($img, true);
        $exif = exif_read_data($path, 0, true);
        $positionDMS = $exif['GPS']["GPS$position"];
        return $this->rationalToFloat($positionDMS[0]) + $this->rationalToFloat($positionDMS[1])/60 + $this->rationalToFloat($positionDMS[2])/3600;
    }

    public function getImageDate($img) {
        $path = $this->vault_filename_fromvalue($img, true);
        $exif = exif_read_data($path, 0, true);
        $dt = new \DateTime($exif['EXIF']["DateTimeOriginal"]);
        return $dt->format(\DateTime::ISO8601);
    }

    protected function rationalToFloat($fraction) {
        $values = explode('/', $fraction);
        if ($values) {
            return doubleval($values[0])/doubleval($values[1]);
        }
        return 0;
    }
}

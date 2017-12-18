<?php


namespace Sample\BusinessApp;

use Dcp\AttributeIdentifiers\BA_FEES as FeesAttr;

class Fees extends \Dcp\Family\Document
{

    public function getCustomTitle()
    {
        $period = $this->getAttributeValue(FeesAttr::fee_period);
        if ($period) {
            return strftime("%B %Y", $period->getTimestamp());
        }
        return parent::getCustomTitle();
    }

    public function postStore()
    {
        require_once("FDL/Lib.Vault.php");
        $outfile = getTmpDir().'/fee-preview.pdf';
        $infile = $this->viewDoc($layout = "THIS:FEE_PREVIEW_TEMPLATE:B","ooo");
        $this->setFile(FeesAttr::fee_odtfile, $infile);
        $this->setValue(FeesAttr::fee_pdffile, $this->convertVaultFile($this->getRawValue(FeesAttr::fee_odtfile), 'pdf'));
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
        $total = $this->getRawValue(FeesAttr::fee_total);
        $advance = $this->getRawValue(FeesAttr::fee_advance);
        $this->lay->eSet('FEE_REPAY', $total - $advance);
    }

    /**
     * Return the Degree Decimal position (latitutde or longitude) of the image attribute
     * @param $img the image attribute
     * @param string $position "Latitude" or "Longitude" position
     * @return float|null the degree decimal value
     */
    public function getImagePosition($img, $position) {
        $path = $this->vault_filename_fromvalue($img, true);
        $exif = exif_read_data($path, 'GPS');
        if (!$exif || !array_key_exists("GPS$position", $exif) || !array_key_exists("GPS$position"."Ref", $exif)) {
            return null;
        }
        $positionDMS = $exif["GPS$position"];
        $positionDMSRef = $exif["GPS$position"."Ref"];
        $fullPosition = Fees::stringifyDMSPosition($positionDMS, $positionDMSRef);
        $positionValue = Fees::convertDec($fullPosition);
        return $positionValue;
    }

    /**
     * Return the date of the image
     * @param $img the image attribute
     * @return null|string the date of the image
     */
    public function getImageDate($img) {
        $path = $this->vault_filename_fromvalue($img, true);
        $exif = exif_read_data($path, "EXIF", true);
        if (!$exif || !array_key_exists('DateTimeOriginal', $exif['EXIF'])) {
            return null;
        }
        $dt = new \DateTime($exif['EXIF']["DateTimeOriginal"]);
        if ($dt) {
            return $dt->format(\DateTime::ISO8601);
        }
        return null;
    }

    /**
     * Return the sum of the all tax amount
     * @param float[] $taxedAmounts the all taxed amounts attribute
     * @return float the computed total
     */
    public function computeTotal($taxedAmounts) {
        if (!empty($taxedAmounts)) {
            $sumArray = function ($carry, $item) {
                $carry += $item;
                return $carry;
            };
            return array_reduce($taxedAmounts, $sumArray, 0);
        }
        return 0;
    }

    /**
     * Stringify the GPS position from the EXIF PHP format to the DMS position format
     * @param string[] $arrayPos rational format (x/y) values of DMS position
     * @param string $posRef "N|E|S|O" position reference
     * @return string the DMS position
     */
    static function stringifyDMSPosition($arrayPos, $posRef) {
        if (!empty($arrayPos) && count($arrayPos) === 3) {
            return Fees::rationalToFloat($arrayPos[0]) . "°"
                . Fees::rationalToFloat($arrayPos[1]) . "'"
                . Fees::rationalToFloat($arrayPos[2]) . "\"" . $posRef;
        }
        return "";
    }

    /**
     * Convert a string representation of a rational value "x/y" to a float value
     * @param string $fraction rational value
     * @return float
     */
    static function rationalToFloat($fraction) {
        $values = explode('/', $fraction);
        if ($values && count($values) === 2) {
            return doubleval($values[0])/doubleval($values[1]);
        }
        return 0.0;
    }

    /**
     * Convert a DMS GPS position to a DD GPS Position
     * @param string $var DMS GPS Position
     * @return float DD GPS Position
     */
    static function convertDec($var) { // Sexagésimal vers décimal

        $var = preg_replace('#([^.a-z0-9]+)#i', '-', $var);
        $tab = explode('-', $var);
        $varD = $tab[0] + ($tab[1] / 60) + ($tab[2] / 3600);
        $pattern = array('n', 's', 'e', 'o', 'N', 'S', 'E', 'O');
        $replace = array('', '-', '', '-', '', '-', '', '-');
        return doubleval(str_replace($pattern, $replace, $tab[3]) . $varD);
    }
}

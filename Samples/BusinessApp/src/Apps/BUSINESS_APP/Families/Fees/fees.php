<?php


namespace Sample\BusinessApp;

use Dcp\AttributeIdentifiers\BA_FEES as FeesAttr;
use Dcp\AttributeIdentifiers\Ba_rh_dir as RHAttr;
use Dcp\AttributeIdentifiers\Ba_categories as CategoriesAttr;
use Dcp\Core\ContextManager;
use Dcp\Core\DbManager;
use Dcp\Core\DocManager;

class Fees extends \Dcp\Family\Document
{

    protected $_categoriesAmountsLimits = null;

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
        $this->setValue(FeesAttr::fee_person, $this->getRHDirFromAccount(ContextManager::getCurrentUser()));
        $this->setValue(FeesAttr::fee_account, $this->getAccount($this->getRawValue(FeesAttr::fee_person)));
        $infile = $this->viewDoc($layout = "THIS:FEE_PREVIEW_TEMPLATE:B","ooo");
        $this->setFile(FeesAttr::fee_odtfile, $infile);
        $this->setValue(FeesAttr::fee_pdffile, $this->convertVaultFile($this->getRawValue(FeesAttr::fee_odtfile), 'pdf'));
        $this->setOutgoingsExceed();
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
        $this->lay->eSet('FEE_TOTAL', $total);
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
            return "";
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

    public function getAccount($rhDirDocument) {
        return DocManager::getRawValue($rhDirDocument, RHAttr::rh_person_account);
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

    protected function getLimitsValues() {
        $docRef = $this->getParameterFamilyRawValue(FeesAttr::fee_limit_values, null);
        if ($docRef) {
            $doc = DocManager::getDocument($docRef);
            if (!empty($doc)) {
                return $doc->getArrayRawValues(CategoriesAttr::cat_t_categories);
            }

        }
        return null;
    }

    protected function setOutgoingsExceed() {
        $currentOutgoings = $this->getArrayRawValues(FeesAttr::fee_t_all_exp);
        $categories = [];
        $this->_categoriesAmountsLimits = $this->getLimitsValues();
        if ($currentOutgoings) {
            for ($i = 0 ; $i < count($currentOutgoings); $i++) {
                $category = $currentOutgoings[$i][FeesAttr::fee_exp_category];
                $date = $currentOutgoings[$i][FeesAttr::fee_exp_date];
                $amount = $currentOutgoings[$i][FeesAttr::fee_exp_tax];
                $exceed = $this->checkOutgoing($category, $date, $amount, $i);
                $this->setValue(FeesAttr::fee_exp_exceed, $exceed, $i);
            }
        }
    }

    protected function checkOutgoing($category, $date, $amount, $positionToIgnore) {
        if (isset($this->_categoriesAmountsLimits)) {
            $outgoingRule = $this->_categoriesAmountsLimits[$category - 1];
            $dateInterval = $this->getDateInterval($date, $outgoingRule[CategoriesAttr::cat_period]);
            $sameCategoryAmount = $this->getCategoryOutgoings($category, $dateInterval, $positionToIgnore);
            if (($sameCategoryAmount + doubleval($amount)) > doubleval($outgoingRule[CategoriesAttr::cat_max])) {
                return "yes";
            }
        }
        return "";
    }

    /**
     * @param string $date date iso format value
     * @param int $period 1 means by year, 2 by month and 3 by day
     * @return array
     */
    protected function getDateInterval($date, $period) {
        $dt = new \DateTime($date);
        $dateBegin = $date;
        $dateEnd = $date;
        if ($period == 1) {
            $year = $dt->format("Y");
            $dateBegin = "$year-01-01";
            $dateEnd = (intval($year)+1)."-01-01";
        } elseif ($period == 2 || $period == 3) {
            $year = $dt->format("Y");
            $month = $dt->format("m");
            $dateBegin = "$year-$month-01";
            $dt->add(new \DateInterval('P1M'));
            $year = $dt->format("Y");
            $month = $dt->format("m");
            $dateEnd = "$year-$month-01";
        }
        return array(
            'start' => $dateBegin,
            'end' => $dateEnd,
            'period' => $period,
            'date' => $date
        );
    }

    protected function getCategoryOutgoings($category, $dateInterval, $positionToIgnore) {
        $searchDoc = new \SearchDoc();
        $searchDoc->fromid = DocManager::getFamilyIdFromName('BA_FEES');
        $searchDoc->setObjectReturn();
        $searchDoc->addFilter(FeesAttr::fee_account." = '%s'", ContextManager::getCurrentUser()->fid);
        $searchDoc->addFilter("%s BETWEEN to_date('%s', 'YYYY-MM-DD') AND to_date('%s', 'YYYY-MM-DD')",
            FeesAttr::fee_period, $dateInterval['start'], $dateInterval['end']);
        $documents = $searchDoc->getDocumentList();
        $total = $this->getCurrentDocCategoryOutgoings($category, $dateInterval, $positionToIgnore);
        foreach ($documents as $key => $doc) {
            if ($this->id != $doc->id) {
                $arrayValues = $doc->getArrayRawValues(FeesAttr::fee_t_all_exp);
                for ($i = 0; $i < count($arrayValues); $i++) {
                    $value = $arrayValues[$i];
                    if ($value[FeesAttr::fee_exp_category] === $category) {
                        if ($dateInterval['period'] === 3) { // daily limit
                            if ($dateInterval['date'] === $value[FeesAttr::fee_exp_date]) {
                                $total += doubleval($value[FeesAttr::fee_exp_tax]);
                            }
                        } else {
                            $total += doubleval($value[FeesAttr::fee_exp_tax]);
                        }
                    }
                }
            }
        }
        return $total;
    }

    protected function getCurrentDocCategoryOutgoings($category, $dateInterval, $positionToIgnore) {
        $arrayValues = $this->getArrayRawValues(FeesAttr::fee_t_all_exp);
        $total = 0;
        for ($i = 0; $i < count($arrayValues); $i++) {
            if ($i != $positionToIgnore) {
                $value = $arrayValues[$i];
                if ($value[FeesAttr::fee_exp_category] === $category) {
                    if ($dateInterval['period'] === 3) { // daily limit
                        if ($dateInterval['date'] === $value[FeesAttr::fee_exp_date]) {
                            $total += doubleval($value[FeesAttr::fee_exp_tax]);
                        }
                    } else {
                        $total += doubleval($value[FeesAttr::fee_exp_tax]);
                    }
                }
            }
        }
        return $total;
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

    protected function getRHDirFromAccount($getCurrentUser)
    {
        $searchDoc = new \SearchDoc();
        $searchDoc->fromid = DocManager::getFamilyIdFromName('BA_RH_DIR');
        $searchDoc->setObjectReturn();
        $searchDoc->addFilter("%s = '%s'", RHAttr::rh_person_account, $getCurrentUser->fid);
        $searchDoc->search();
        $rhdirdoc = $searchDoc->getNextDoc();
        if ($rhdirdoc) {
            return $rhdirdoc->id;
        }
        return null;

    }
}

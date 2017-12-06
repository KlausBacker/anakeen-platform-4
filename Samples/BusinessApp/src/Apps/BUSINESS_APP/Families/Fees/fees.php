<?php


namespace Sample\BusinessApp;

use Dcp\AttributeIdentifiers\BA_FEES as FeesAttr;

class Fees extends \Dcp\Family\Document
{
    /**
     * Compute TVA
     * @param $preTaxedAmount
     * @param $taxedAmount
     * @return float|int|string
     */
    public function computeTVA($preTaxedAmount, $taxedAmount) {
        if (!empty($preTaxedAmount) && !empty($taxedAmount)) {
            return ((($taxedAmount/$preTaxedAmount) - 1)*100)." %";
        }
        return " ";
    }

    public function getCustomTitle()
    {
        $period = $this->getAttributeValue(FeesAttr::fee_period);
        return sprintf("Note de frais %s", $period);
    }
}

<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Ui;

class DoubleRenderOptions extends IntRenderOptions
{
    
    const type = "double";
    const decimalPrecisionOption = "decimalPrecision";
    /**
     * Number of digit after comma
     * @param int $number
     * @return $this
     */
    public function setDecimalPrecision($number)
    {
        return $this->setOption(self::decimalPrecisionOption, (int)$number);
    }
}

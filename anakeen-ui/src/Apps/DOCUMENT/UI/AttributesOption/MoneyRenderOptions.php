<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Ui;

class MoneyRenderOptions extends DoubleRenderOptions
{
    
    const type = "money";
    const currencyOption = "currency";
    /**
     * Currency character
     * @param string $currency
     * @return $this
     */
    public function setCurrency($currency)
    {
        return $this->setOption(self::currencyOption, $currency);
    }
}

<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
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

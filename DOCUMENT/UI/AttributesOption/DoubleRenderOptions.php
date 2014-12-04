<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
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

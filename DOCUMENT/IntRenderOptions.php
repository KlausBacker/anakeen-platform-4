<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Dcp\Ui;

class IntRenderOptions extends CommonRenderOptions
{
    
    const type = "int";
    const maxOption = "max";
    const minOption = "min";
    /**
     * Maximum limit that number can reach
     * @note use only in edition mode
     * @param int $number (null if no limit)
     * @return $this
     */
    public function setMax($number)
    {
        if ($number !== null) {
            $number = (int)$number;
        }
        return $this->setOption(self::maxOption, $number);
    }
    /**
     * Minimum number limit
     * @note use only in consultation mode
     * @param int $number (null if no limit)
     * @return $this
     */
    public function setMin($number)
    {
        if ($number !== null) {
            $number = (int)$number;
        }
        return $this->setOption(self::minOption, $number);
    }
}

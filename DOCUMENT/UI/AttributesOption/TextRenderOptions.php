<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Dcp\Ui;

class TextRenderOptions extends CommonRenderOptions
{
    
    const type = "text";
    const sizeOption = "size";
    const formatOption = "format";
    /**
     * Number of characters for the input
     * @note use only in edition mode
     * @param int $number
     * @return $this
     */
    public function size($number)
    {
        return $this->setOption(self::sizeOption, (int)$number);
    }
    /**
     * Forrmat use to decorate string
     * @note use only in consultation mode
     * @param string $format
     * @return $this
     */
    public function format($format)
    {
        return $this->setOption(self::formatOption, $format);
    }
}

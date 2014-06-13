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
    
    public function size($number)
    {
        return $this->setOption(self::sizeOption, $number);
    }
    public function format($format)
    {
        return $this->setOption(self::formatOption, $format);
    }
}

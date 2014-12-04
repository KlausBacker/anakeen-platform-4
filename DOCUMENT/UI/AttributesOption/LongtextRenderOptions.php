<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Dcp\Ui;

class LongtextRenderOptions extends CommonRenderOptions
{
    
    const type = "longtext";
    const displayedLineNumberOption = "displayedLineNumber";
    /**
     * Number of line which are displayed before a scroll bar appears
     * @note use only in edition mode
     * @param int $number ( 0 if no limit)
     * @return $this
     */
    public function setdisplayedLineNumber($number)
    {
        
        return $this->setOption(self::displayedLineNumberOption, (int)$number);
    }
}

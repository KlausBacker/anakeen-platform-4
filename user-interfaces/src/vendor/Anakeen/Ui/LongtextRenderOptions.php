<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Anakeen\Ui;

class LongtextRenderOptions extends TextRenderOptions
{
    
    const type = "longtext";
    const displayedLineNumberOption = "displayedLineNumber";
    /**
     * Number of line which are displayed before a scroll bar appears
     * The number of line text cannot up to $number
     * @note use only in edition mode
     * @param int $number ( 0 if no limit)
     * @throws Exception UI0204
     * @return $this
     */
    public function setMaxDisplayedLineNumber($number)
    {
        if (!is_int($number) || $number < 0) {
            throw new Exception("UI0204", $number);
        }
        return $this->setOption(self::displayedLineNumberOption, (int)$number);
    }
}

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
    const maxLengthOption = "maxLength";
    const placeHolderOption = "placeHolder";
    const formatOption = "format";

    /**
     * Max number of characters for the input
     * @note use only in edition mode
     * @param int $number
     * @throws Exception
     * @return $this
     */
    public function setMaxLength($number)
    {
        if (! is_int($number) || $number < 0) {
            throw new Exception("UI0203", $number);
        }
        return $this->setOption(self::maxLengthOption, (int)$number);
    }
    /**
     * Text to set into input when is empty
     * @note use only in edition mode
     * @param string $text text to display
     * @return $this
     */
    public function setPlaceHolder($text)
    {
        return $this->setOption(self::placeHolderOption, $text);
    }
    /**
     * Format use to decorate string
     * @note use only in consultation mode
     * @param string $format
     * @return $this
     */
    public function setFormat($format)
    {
        return $this->setOption(self::formatOption, $format);
    }
}

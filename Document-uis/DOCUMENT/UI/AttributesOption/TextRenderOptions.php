<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Ui;

class TextRenderOptions extends CommonRenderOptions
{
    use TFormatRenderOption;
    const type = "text";
    const maxLengthOption = "maxLength";
    const formatOption = "format";
    const kendoAutoCompleteConfigurationOption = "kendoAutoCompleteConfiguration";
    /**
     * Max number of characters for the input
     * @note use only in edition mode
     * @param int $number
     * @throws Exception
     * @return $this
     */
    public function setMaxLength($number)
    {
        if (!is_int($number) || $number < 0) {
            throw new Exception("UI0203", $number);
        }
        return $this->setOption(self::maxLengthOption, (int)$number);
    }
    /**
     * Set extra configuration for kendoAutoComplete widget
     *
     * @note use only in edition mode
     * @param array $config indexed array
     *
     * @return $this
     */
    public function setKendoAutoCompleteConfiguration($config)
    {
        $opt = $this->getOption(self::kendoAutoCompleteConfigurationOption);
        if (is_array($opt)) {
            $config = array_merge($opt, $config);
        }
        return $this->setOption(self::kendoAutoCompleteConfigurationOption, $config);
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
}

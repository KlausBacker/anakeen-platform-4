<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Anakeen\Ui;

class IntRenderOptions extends CommonRenderOptions
{
    use TFormatRenderOption;
    const type = "int";
    const maxOption = "max";
    const minOption = "min";
    const kendoNumericConfigurationOption = "kendoNumericConfiguration";
    const formatOption = "format";
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
    /**
     * Set extra configuration for kendoNumericTextBox widget
     *
     * @note use only in edition mode
     * @param array $config indexed array
     *
     * @return $this
     */
    public function setKendoNumericConfiguration($config)
    {
        $opt = $this->getOption(self::kendoNumericConfigurationOption);
        if (is_array($opt)) {
            $config = array_merge($opt, $config);
        }
        return $this->setOption(self::kendoNumericConfigurationOption, $config);
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

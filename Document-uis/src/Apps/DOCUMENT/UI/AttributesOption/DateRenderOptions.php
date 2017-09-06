<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Ui;

class DateRenderOptions extends CommonRenderOptions
{
    use TFormatRenderOption;
    const type = "date";
    const kendoDateConfigurationOption = "kendoDateConfiguration";
    /**
     * Set extra configuration for kendoDate widget
     *
     * @note use only in edition mode
     * @param array $config indexed array
     *
     * @return $this
     */
    public function setKendoDateConfiguration($config)
    {
        return $this->setOption(self::kendoDateConfigurationOption, $config);
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

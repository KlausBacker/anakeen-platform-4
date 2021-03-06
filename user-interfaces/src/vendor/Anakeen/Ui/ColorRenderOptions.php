<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Anakeen\Ui;

class ColorRenderOptions extends CommonRenderOptions
{
    
    const type = "color";
    const kendoColorConfigurationOption = "kendoColorConfiguration";


    /**
     * Set extra configuration for kendoColorPicker widget
     *
     * @note use only in edition mode
     * @param array $config indexed array
     *
     * @return $this
     */
    public function setKendoColorConfiguration($config)
    {
        return $this->setOption(self::kendoColorConfigurationOption, $config);
    }
}

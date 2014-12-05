<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Dcp\Ui;

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

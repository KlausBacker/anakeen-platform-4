<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Dcp\Ui;

class TimeRenderOptions extends CommonRenderOptions
{
    
    const type = "time";
    const kendoTimeConfigurationOption = "kendoTimeConfiguration";
    /**
     * Set extra configuration for kendoTime widget
     *
     * @note use only in edition mode
     * @param array $config indexed array
     *
     * @return $this
     */
    public function setKendoTimeConfiguration($config)
    {
        return $this->setOption(self::kendoTimeConfigurationOption, $config);
    }
}

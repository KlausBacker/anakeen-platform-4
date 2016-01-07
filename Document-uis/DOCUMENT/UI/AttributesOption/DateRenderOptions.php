<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
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

}

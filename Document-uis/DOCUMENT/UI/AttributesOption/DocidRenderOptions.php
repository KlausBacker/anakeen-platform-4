<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Dcp\Ui;

class DocidRenderOptions extends CommonRenderOptions
{
    
    use TFormatRenderOption;
    const type = "docid";
    const kendoMultiSelectConfigurationOption = "kendoMultiSelectConfiguration";
    /**
     * Set extra configuration for kendoMultiSelect widget
     *
     * @note use only in edition mode
     * @param array $config indexed array
     *
     * @return $this
     */
    public function setKendoMultiSelectConfiguration($config)
    {
        $opt = $this->getOption(self::kendoMultiSelectConfigurationOption);
        if (is_array($opt)) {
            $config = array_merge($opt, $config);
        }
        return $this->setOption(self::kendoMultiSelectConfigurationOption, $config);
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

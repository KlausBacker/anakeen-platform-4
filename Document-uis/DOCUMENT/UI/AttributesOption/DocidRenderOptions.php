<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Ui;

class DocidRenderOptions extends CommonRenderOptions
{
    
    use TFormatRenderOption;
    const type = "docid";
    const kendoMultiSelectConfigurationOption = "kendoMultiSelectConfiguration";
    const listDisplay = "list";
    const autocompletionDisplay = "autoCompletion";
    const multipleSingleDisplay = "singleMultiple";
    const displayOption = "editDisplay";
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

    /**
     * Display format
     * @note use only in edition mode
     * @param string $display one of vertical, horizontal, select, autoCompletion, bool
     * @return $this
     * @throws Exception
     */
    public function setDisplay($display)
    {
        $allow = array(
            self::listDisplay,
            self::multipleSingleDisplay,
            self::autocompletionDisplay
        );
        if (!in_array($display, $allow)) {
            throw new Exception("UI0210", $display, implode(', ', $allow));
        }
        return $this->setOption(self::displayOption, $display);
    }
}

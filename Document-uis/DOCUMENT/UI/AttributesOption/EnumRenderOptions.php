<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Dcp\Ui;

class EnumRenderOptions extends CommonRenderOptions
{
    
    const type = "enum";
    const displayOption = "editDisplay";
    const useFirstChoiceOption = "useFirstChoice";
    const useSourceUriOption = "useSourceUri";
    const useOtherChoiceOption = "useOtherChoice";
    const verticalDisplay = "vertical";
    const horizontalDisplay = "horizontal";
    const listDisplay = "list";
    const autocompletionDisplay = "autoCompletion";
    const boolDisplay = "bool";
    const sortByOption = "sortBy";
    const sortByKeyOption = "key";
    const sortByLabelOption = "label";
    const sortByOrderOption = "none";
    /**
     * Display formet
     * @note use only in edition mode
     * @param string $display one of vertical, horizontal, select, autoCompletion, bool
     * @return $this
     * @throws Exception
     */
    public function setDisplay($display)
    {
        $allow = array(
            self::verticalDisplay,
            self::horizontalDisplay,
            self::listDisplay,
            self::autocompletionDisplay,
            self::boolDisplay
        );
        if (!in_array($display, $allow)) {
            throw new Exception("UI0200", $display, implode(', ', $allow));
        }
        return $this->setOption(self::displayOption, $display);
    }
    /**
     * No use first choice if no value unless if a default value is configured
     * @note use only in edition mode
     * @param bool $useIt
     * @return $this
     * @throws Exception
     */
    public function useSourceUri($useIt)
    {
        return $this->setOption(self::useSourceUriOption, (bool)$useIt);
    }
    /**
     * Add input to set alternative choice
     * @note use only in edition mode
     * @param bool $useIt
     * @return $this
     * @throws Exception
     */
    public function useOtherChoice($useIt)
    {
        return $this->setOption(self::useOtherChoiceOption, (bool)$useIt);
    }
    /**
     * No use first choice if no value unless if a default value is configured
     * @note use only in edition mode
     * @param bool $useIt
     * @return $this
     * @throws Exception
     */
    public function useFirstChoice($useIt)
    {
        return $this->setOption(self::useFirstChoiceOption, (bool)$useIt);
    }
    public function setSortBy($sortBy)
    {
        $allow = array(
            self::sortByOrderOption,
            self::sortByLabelOption,
            self::sortByKeyOption
        );
        if (!in_array($sortBy, $allow)) {
            throw new Exception("UI0209", $sortBy, implode(', ', $allow));
        }
        return $this->setOption(self::sortByOption, $sortBy);
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

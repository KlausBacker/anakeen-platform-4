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
    const orderByOption = "orderBy";
    const orderByKeyOption = "key";
    const orderByLabelOption = "label";
    const orderByOrderOption = "none";
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

    /**
     * Order enum by label, key or internal order option
     * @param string $orderBy
     *
     * @return $this
     * @throws Exception
     */
    public function setOrderBy($orderBy)
    {
        $allow = array(
            self::orderByOrderOption,
            self::orderByLabelOption,
            self::orderByKeyOption
        );
        if (!in_array($orderBy, $allow)) {
            throw new Exception("UI0209", $orderBy, implode(', ', $allow));
        }
        return $this->setOption(self::orderByOption, $orderBy);
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

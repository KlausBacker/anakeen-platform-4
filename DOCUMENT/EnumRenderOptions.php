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
    
    const verticalDisplay = "vertical";
    const horizontalDisplay = "horizontal";
    const selectDisplay = "select";
    const autocompletionDisplay = "autoCompletion";
    const boolDisplay = "bool";
    /**
     * Display formet
     * @note use only in edition mode
     * @param string $display one of vertical, horizontal, select, autoCompletion, bool
     * @return $this
     * @throws Exception
     */
    public function display($display)
    {
        $allow = array(
            self::verticalDisplay,
            self::horizontalDisplay,
            self::selectDisplay,
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
    public function useFirstChoice($useIt)
    {
        return $this->setOption(self::useFirstChoiceOption, (bool)$useIt);
    }
}

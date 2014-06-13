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
    const displayOption = "display";
    const useFirstChoiceOption = "useFirstChoice";
    
    const verticalDisplay = "vertical";
    const horizontalDisplay = "horizontal";
    const selectDisplay = "select";
    const autocompletionDisplay = "autoCompletion";
    const boolDisplay = "bool";
    
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
     * In edition mode : No use first choice unless if a default value is configured
     * @param $useIt
     * @return $this
     * @throws Exception
     */
    public function useFirstChoice($useIt)
    {
        return $this->setOption(self::useFirstChoiceOption, (bool)$useIt);
    }
}

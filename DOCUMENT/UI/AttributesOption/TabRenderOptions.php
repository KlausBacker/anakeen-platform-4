<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Dcp\Ui;

class TabRenderOptions extends CommonRenderOptions
{
    
    const type = "tab";
    const openFirstOption = "openFirst";
    const tabPlacementOption = "tabPlacement";
    const tabLeftPlacement = "left";
    const tabTopPlacement = "top";
    const tabTopFixPlacement = "topFix"; // fix width and display
    
    /**
     * Open this tab on render first
     *
     * Can be use only with specific tab attribute
     * @param bool $openIt open it
     * @return $this
     */
    public function setOpenFirst($openIt = true)
    {
        return $this->setOption(self::openFirstOption, (bool)$openIt);
    }
    /**
     * Placement of tab labels
     *
     * @note The value cannot be apply to a particular tab but for all tabs
     *
     * @param string $tabPlacement top (default) or right
     *
     * @throws Exception UI0107
     * @return $this
     */
    public function setTabPlacement($tabPlacement)
    {
        $allowPlacement = array(
            self::tabLeftPlacement,
            self::tabTopPlacement,
            self::tabTopFixPlacement
        );
        if (!in_array($tabPlacement, $allowPlacement)) {
            throw new Exception("UI0107", $tabPlacement, implode(', ', $allowPlacement));
        }
        return $this->setOption(self::tabPlacementOption, $tabPlacement);
    }
}

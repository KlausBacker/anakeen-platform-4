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
    const tabTooltipLabel = "tooltipLabel";
    const tabTooltipHtml = "tooltipHtml";
    /**
     * Set tooltip on a tab
     * @param string $label tooltip text
     * @param bool $html set to true if it is a html fragment
     *
     * @return $this
     */
    public function setTooltipLabel($label, $html = false)
    {
        $this->setOption(self::tabTooltipLabel, $label);
        return $this->setOption(self::tabTooltipHtml, (bool)$html);
    }
}

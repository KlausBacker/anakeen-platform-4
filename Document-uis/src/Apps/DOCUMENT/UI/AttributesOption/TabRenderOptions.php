<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Ui;

class TabRenderOptions extends CommonRenderOptions
{
    
    const type = "tab";
    const tabTooltipLabel = "tooltipLabel";
    const tabTooltipHtml = "tooltipHtml";
    const responsiveColumnsOption = "responsiveColumns";
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


    /**
     * Set interval length condition to divide tab frame's in column
     * Direction is left to right
     * number : column number between 2 to 12
     * minWidth : min width of frame div to use (included)
     * maxWidth : max width of frame div to use (excluded)
     * grow : grow width in last row is missing attributes to complete row
     *
     * @param array $responsives
     */
    public function setResponsiveColumns(array $responsives)
    {
        $columns = array();
        $previousMax="0";
        foreach ($responsives as $responsive) {
            if (!$responsive["minWidth"]) {
                $responsive["minWidth"]=$previousMax;
            }
            $columns[] = [
                "number" => $responsive["number"],
                "minWidth" => $responsive["minWidth"],
                "maxWidth" => $responsive["maxWidth"],
                "grow" => $responsive["grow"],
            ];
            $previousMax=$responsive["maxWidth"];
        }
        $this->setOption(self::responsiveColumnsOption, $columns);
    }
}

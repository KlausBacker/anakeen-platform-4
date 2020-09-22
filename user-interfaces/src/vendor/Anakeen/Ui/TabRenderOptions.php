<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Anakeen\Ui;

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
     * Add an html text near the tab
     *
     * @param string $htmlTitle   Html text short description
     * @param string $position    position : top, bottom, click
     *
     * @param string $htmlContent Html text long description
     * @param bool   $collapsed   if true the long description is collapsed (need click to see it)
     *
     * @return $this
     * @throws Exception
     */
    public function setDescription($htmlTitle, $position = "top", $htmlContent = "", $collapsed = false)
    {
        $allow = array(
            self::topPosition,
            self::bottomPosition,
            self::clickPosition
        );
        if (!in_array($position, $allow)) {
            throw new Exception("UI0217", $position, implode(', ', $allow));
        }

        parent::setDescription($htmlTitle, $position, $htmlContent, $collapsed);
        return $this;
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
            if (empty($responsive["minWidth"])) {
                $responsive["minWidth"]=$previousMax;
            }
            $columns[] = [
                "number" => $responsive["number"],
                "minWidth" => empty($responsive["minWidth"])?null:$responsive["minWidth"],
                "maxWidth" => isset($responsive["maxWidth"])?$responsive["maxWidth"]:null,
                "grow" => !empty($responsive["grow"]),
            ];
            $previousMax=empty($responsive["maxWidth"])?null:$responsive["maxWidth"];
        }
        $this->setOption(self::responsiveColumnsOption, $columns);
    }
}

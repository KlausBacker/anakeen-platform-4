<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Anakeen\Ui;

class FrameRenderOptions extends CommonRenderOptions
{

    const type = "frame";
    const collapseOption = "collapse";

    const collapseNone = "none";
    const collapseExpanded = "expand";
    const collapseCollapsed = "collapse";
    const responsiveColumnsOption = "responsiveColumns";
    const topBottomDirection="topBottom";
    const leftRightDirection="leftRight";

    /**
     * Expand / Collapse frame content
     *
     * @param string $collapse "collapse" to collapse, "expand" (default) to expand, "none" to inhibit collapse
     *
     * @return $this
     * @throws Exception
     */
    public function setCollapse($collapse)
    {
        $allow = array(
            self::collapseNone,
            self::collapseExpanded,
            self::collapseCollapsed
        );
        // For compatibility
        if ($collapse === true) {
            $collapse = self::collapseCollapsed;
        } elseif ($collapse === false) {
            $collapse = self::collapseExpanded;
        }

        if (!in_array($collapse, $allow)) {
            throw new Exception("UI0213", $collapse, implode(', ', $allow));
        }
        return $this->setOption(self::collapseOption, $collapse);
    }
    /**
     * Add an html text near the frame
     *
     * @param string $htmlTitle   Html text short description
     * @param string $position    position : top, bottom, bottomLabel, click
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
            self::bottomLabelPosition,
            self::clickPosition
        );
        if (!in_array($position, $allow)) {
            throw new Exception("UI0216", $position, implode(', ', $allow));
        }

        parent::setDescription($htmlTitle, $position, $htmlContent, $collapsed);
        return $this;
    }

    /**
     * Set interval length condition to divide frame attributes in column
     * number : column number between 2 to 12
     * minWidth : min width of frame div to use (included)
     * maxWidth : max width of frame div to use (excluded)
     * direction : leftRight  or topBottom (default is leftRight)
     * grow : in leftRight : grow width in last row is missing attributes to complete row
     *     in topBottom : decrease column number if last columns are empty
     * @param array $responsives
     * @return FrameRenderOptions
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
                "direction" => empty($responsive["direction"])?self::leftRightDirection:$responsive["direction"],
            ];
            $previousMax=empty($responsive["maxWidth"])?null:$responsive["maxWidth"];
        }
        return $this->setOption(self::responsiveColumnsOption, $columns);
    }
}

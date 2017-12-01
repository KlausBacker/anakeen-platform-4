<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Ui;

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
     * Set interval length condition to divide frame attributes in column
     * number : column number between 2 to 12
     * minWidth : min width of frame div to use (included)
     * maxWidth : max width of frame div to use (excluded)
     * direction : leftRight  or topBottom
     * grow : in leftRight : grow width in last row is missing attributes to complete row
     *     in topBottom : decrease column number if last columns are empty
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
                "direction" => $responsive["direction"],
            ];
            $previousMax=$responsive["maxWidth"];
        }
        $this->setOption(self::responsiveColumnsOption, $columns);
    }
}

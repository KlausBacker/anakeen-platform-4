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
            $collapse=self::collapseCollapsed;
        } elseif ($collapse === false) {
            $collapse=self::collapseExpanded;
        }

        if (!in_array($collapse, $allow)) {
            throw new Exception("UI0213", $collapse, implode(', ', $allow));
        }
        return $this->setOption(self::collapseOption, $collapse);
    }
}

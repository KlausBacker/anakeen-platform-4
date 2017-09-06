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
    /**
     * Expand / Collapse frame content
     *
     * @param bool $expand false to collapse, true (default) to expand
     * @return $this
     */
    public function setCollapse($expand)
    {
        return $this->setOption(self::collapseOption, (bool)$expand);
    }
}

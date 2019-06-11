<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Anakeen\SmartStructures\Helppage\Render;

class HelppageAccess implements \Anakeen\Ui\IRenderConfigAccess
{
    /**
     * @param string $mode
     * @return \Anakeen\Ui\IRenderConfig
     */
    public function getRenderConfig($mode, \Anakeen\Core\Internal\SmartElement $document)
    {
        switch ($mode) {
            case \Anakeen\Ui\RenderConfigManager::CreateMode:
            case \Anakeen\Ui\RenderConfigManager::EditMode:
                return new HelppageEditRender();
            case \Anakeen\Ui\RenderConfigManager::ViewMode:
                return new HelppageViewRender();
        }
        return null;
    }
}

<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Anakeen\SmartStructures\RenderDescription\Render;

class RenderDescriptionAccess implements \Anakeen\Ui\IRenderConfigAccess
{
    /**
     * @param string $mode
     * @return \Anakeen\Ui\IRenderConfig
     */
    public function getRenderConfig($mode, \Anakeen\Core\Internal\SmartElement $document)
    {
        switch ($mode) {
            case \Anakeen\Ui\RenderConfigManager::CreateMode:
                return new RenderDescriptionCreateRender();
            case \Anakeen\Ui\RenderConfigManager::EditMode:
                return new RenderDescriptionEditRender();
            case \Anakeen\Ui\RenderConfigManager::ViewMode:
                return new RenderDescriptionViewRender();
        }
        return null;
    }
}

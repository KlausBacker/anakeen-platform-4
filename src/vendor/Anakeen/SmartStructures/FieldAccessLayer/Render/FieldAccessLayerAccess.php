<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Anakeen\SmartStructures\FieldAccessLayer\Render;

class FieldAccessLayerAccess implements \Dcp\Ui\IRenderConfigAccess
{
    /**
     * @param string $mode
     * @return FieldAccessLayerEditRender|FieldAccessLayerViewRender|null
     */
    public function getRenderConfig($mode, \Anakeen\Core\Internal\SmartElement $document)
    {
        switch ($mode) {
            case \Dcp\Ui\RenderConfigManager::CreateMode:
            case \Dcp\Ui\RenderConfigManager::EditMode:
                return new FieldAccessLayerEditRender();
            case \Dcp\Ui\RenderConfigManager::ViewMode:
                return new FieldAccessLayerViewRender();
        }
        return null;
    }
}

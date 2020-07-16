<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Anakeen\SmartStructures\FieldAccessLayer\Render;

class FieldAccessLayerAccess implements \Anakeen\Ui\IRenderConfigAccess
{
    /**
     * @param string $mode
     * @return FieldAccessLayerEditRender|FieldAccessLayerViewRender|null
     */
    public function getRenderConfig($mode, \Anakeen\Core\Internal\SmartElement $document)
    {
        switch ($mode) {
            case \Anakeen\Ui\RenderConfigManager::CreateMode:
            case \Anakeen\Ui\RenderConfigManager::EditMode:
                return new FieldAccessLayerEditRender();
            case \Anakeen\Ui\RenderConfigManager::ViewMode:
                return new FieldAccessLayerViewRender();
        }
        return null;
    }
}

<?php

namespace Anakeen\SmartStructures\FieldAccessLayerList\Render;

class FieldAccessLayerListAccess implements \Anakeen\Ui\IRenderConfigAccess
{
    /**
     * @param string                              $mode
     * @param \Anakeen\Core\Internal\SmartElement $document
     * @return FieldAccessLayerListEditRender|FieldAccessLayerListViewRender|null
     */
    public function getRenderConfig($mode, \Anakeen\Core\Internal\SmartElement $document)
    {
        switch ($mode) {
            case \Anakeen\Ui\RenderConfigManager::CreateMode:
            case \Anakeen\Ui\RenderConfigManager::EditMode:
                return new FieldAccessLayerListEditRender();
            case \Anakeen\Ui\RenderConfigManager::ViewMode:
                return new FieldAccessLayerListViewRender();
        }
        return null;
    }
}

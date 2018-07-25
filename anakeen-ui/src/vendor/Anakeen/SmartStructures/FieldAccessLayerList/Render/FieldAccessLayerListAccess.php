<?php

namespace Anakeen\SmartStructures\FieldAccessLayerList\Render;

class FieldAccessLayerListAccess implements \Dcp\Ui\IRenderConfigAccess
{
    /**
     * @param string                              $mode
     * @param \Anakeen\Core\Internal\SmartElement $document
     * @return FieldAccessLayerListEditRender|FieldAccessLayerListViewRender|null
     */
    public function getRenderConfig($mode, \Anakeen\Core\Internal\SmartElement $document)
    {
        switch ($mode) {
            case \Dcp\Ui\RenderConfigManager::CreateMode:
            case \Dcp\Ui\RenderConfigManager::EditMode:
                return new FieldAccessLayerListEditRender();
            case \Dcp\Ui\RenderConfigManager::ViewMode:
                return new FieldAccessLayerListViewRender();
        }
        return null;
    }
}

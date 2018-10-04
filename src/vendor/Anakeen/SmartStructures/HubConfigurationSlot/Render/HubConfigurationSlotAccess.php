<?php

namespace Anakeen\SmartStructures\HubConfigurationSlot\Render;

class HubConfigurationSlotAccess implements \Dcp\Ui\IRenderConfigAccess
{
    /**
     * @param string                              $mode
     * @param \Anakeen\Core\Internal\SmartElement $document
     * @return \Dcp\Ui\IRenderConfig
     */
    public function getRenderConfig($mode, \Anakeen\Core\Internal\SmartElement $document)
    {
        switch ($mode) {
            case \Dcp\Ui\RenderConfigManager::CreateMode:
            case \Dcp\Ui\RenderConfigManager::EditMode:
                return new HubConfigurationSlotEditRender();
            case \Dcp\Ui\RenderConfigManager::ViewMode:
                return new HubConfigurationSlotViewRender();
        }
        return null;
    }
}

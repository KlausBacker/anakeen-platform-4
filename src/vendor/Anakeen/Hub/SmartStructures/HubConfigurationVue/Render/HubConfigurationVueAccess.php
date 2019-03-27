<?php

namespace Anakeen\Hub\SmartStructures\HubConfigurationVue\Render;

class HubConfigurationVueAccess implements \Anakeen\Ui\IRenderConfigAccess
{
    /**
     * @param string                              $mode
     * @param \Anakeen\Core\Internal\SmartElement $document
     * @return \Anakeen\Ui\IRenderConfig
     */
    public function getRenderConfig($mode, \Anakeen\Core\Internal\SmartElement $document)
    {
        switch ($mode) {
            case \Anakeen\Ui\RenderConfigManager::CreateMode:
            case \Anakeen\Ui\RenderConfigManager::EditMode:
                return new HubConfigurationVueEditRender();
            case \Anakeen\Ui\RenderConfigManager::ViewMode:
                return new HubConfigurationVueViewRender();
        }
        return null;
    }
}

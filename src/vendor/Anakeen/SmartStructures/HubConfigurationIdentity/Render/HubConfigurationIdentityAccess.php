<?php

namespace Anakeen\SmartStructures\HubConfigurationIdentity\Render;

class HubConfigurationIdentityAccess implements \Dcp\Ui\IRenderConfigAccess
{
    /**
     * @param string $mode
     * @return \Dcp\Ui\IRenderConfig
     */
    public function getRenderConfig($mode, \Anakeen\Core\Internal\SmartElement $document)
    {
        switch ($mode) {
            case \Dcp\Ui\RenderConfigManager::CreateMode:
            case \Dcp\Ui\RenderConfigManager::EditMode:
                return new HubConfigurationIdentityEditRender();
            case \Dcp\Ui\RenderConfigManager::ViewMode:
                return new HubConfigurationIdentityViewRender();
        }
        return null;
    }
}

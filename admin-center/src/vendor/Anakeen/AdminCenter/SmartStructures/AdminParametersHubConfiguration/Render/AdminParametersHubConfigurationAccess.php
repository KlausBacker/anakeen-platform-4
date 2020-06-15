<?php

namespace Anakeen\AdminCenter\SmartStructures\AdminParametersHubConfiguration\Render;

use Anakeen\Core\Internal\SmartElement;
use Anakeen\Ui\RenderConfigManager;

class AdminParametersHubConfigurationAccess implements \Anakeen\Ui\IRenderConfigAccess
{
    /**
     * @param string $mode
     * @param SmartElement $document
     * @return \Anakeen\Ui\IRenderConfig
     */
    public function getRenderConfig($mode, SmartElement $document)
    {
        switch ($mode) {
            case RenderConfigManager::CreateMode:
            case RenderConfigManager::EditMode:
                return new AdminParametersHubConfigurationEditRender();
            case RenderConfigManager::ViewMode:
                return new AdminParametersHubConfigurationViewRender();
        }
        return null;
    }
}

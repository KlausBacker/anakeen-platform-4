<?php

namespace Anakeen\Hub\SmartStructures\HubConfigurationGeneric\Render;

use Anakeen\Core\Internal\SmartElement;
use Anakeen\Ui\RenderConfigManager;

class HubConfigurationGenericAccess implements \Anakeen\Ui\IRenderConfigAccess
{
    /**
     * @param string                              $mode
     * @param SmartElement $document
     * @return \Anakeen\Ui\IRenderConfig
     */
    public function getRenderConfig($mode, SmartElement $document)
    {
        switch ($mode) {
            case RenderConfigManager::CreateMode:
            case RenderConfigManager::EditMode:
                return new HubConfigurationGenericEditRender();
            case RenderConfigManager::ViewMode:
                return new HubConfigurationGenericViewRender();
        }
        return null;
    }
}

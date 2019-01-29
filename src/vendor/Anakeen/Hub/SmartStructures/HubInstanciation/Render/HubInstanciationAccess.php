<?php

namespace Anakeen\Hub\SmartStructures\HubInstanciation\Render;

class HubInstanciationAccess implements \Dcp\Ui\IRenderConfigAccess
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
                return new HubInstanciationEditRender();
            case \Dcp\Ui\RenderConfigManager::ViewMode:
                return new HubInstanciationViewRender();
        }
        return null;
    }
}

<?php

namespace Anakeen\SmartStructures\Msearch\Render;

class MSearchAccess implements \Dcp\Ui\IRenderConfigAccess
{
    /**
     * @param string                              $mode
     * @param \Anakeen\Core\Internal\SmartElement $document
     *
     * @return \Dcp\Ui\IRenderConfig
     */
    public function getRenderConfig($mode, \Anakeen\Core\Internal\SmartElement $document)
    {
        switch ($mode) {
            case \Dcp\Ui\RenderConfigManager::CreateMode:
            case \Dcp\Ui\RenderConfigManager::EditMode:
                return new MSearchEditRender();
            case \Dcp\Ui\RenderConfigManager::ViewMode:
                return new MSearchViewRender();
        }
        return null;
    }
}
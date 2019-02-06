<?php

namespace Anakeen\SmartStructures\Dsearch\Render;

class DSearchAccess implements \Anakeen\Ui\IRenderConfigAccess
{
    /**
     * @param string $mode
     * @return \Anakeen\Ui\IRenderConfig
     */
    public function getRenderConfig($mode, \Anakeen\Core\Internal\SmartElement $document)
    {
        switch ($mode) {
            case \Anakeen\Ui\RenderConfigManager::CreateMode:
            case \Anakeen\Ui\RenderConfigManager::EditMode:
                return new SearchEditRender();
            case \Anakeen\Ui\RenderConfigManager::ViewMode:
                return new SearchViewRender();
        }

        return null;
    }
}

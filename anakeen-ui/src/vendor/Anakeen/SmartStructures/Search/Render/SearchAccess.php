<?php

namespace Anakeen\SmartStructures\Search\Render;

class SearchAccess implements \Dcp\Ui\IRenderConfigAccess
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
                return new SearchEditRender();
            case \Dcp\Ui\RenderConfigManager::ViewMode:
                return new SearchViewRender();
        }
        return null;
    }
}
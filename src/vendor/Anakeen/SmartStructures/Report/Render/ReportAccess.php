<?php

namespace Anakeen\SmartStructures\Report\Render;

class ReportAccess implements \Dcp\Ui\IRenderConfigAccess
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
                return new ReportEditRender();
            case \Dcp\Ui\RenderConfigManager::ViewMode:
                return new ReportViewRender();
        }

        return null;
    }
}

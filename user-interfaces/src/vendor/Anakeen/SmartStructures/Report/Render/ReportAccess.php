<?php

namespace Anakeen\SmartStructures\Report\Render;

class ReportAccess implements \Anakeen\Ui\IRenderConfigAccess
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
                return new ReportEditRender();
            case \Anakeen\Ui\RenderConfigManager::ViewMode:
                return new ReportViewRender();
        }

        return null;
    }
}

<?php

namespace SearchUI;

class ReportAccess implements \Dcp\Ui\IRenderConfigAccess
{
    /**
     * @param string $mode
     * @return \Dcp\Ui\IRenderConfig
     */
    public function getRenderConfig($mode,  \Anakeen\Core\Internal\SmartElement $document)
    {
        switch ($mode) {
            case \Dcp\Ui\RenderConfigManager::CreateMode:
            case \Dcp\Ui\RenderConfigManager::EditMode:
                return new \Dcp\Search\html5\Report_html5_edit_render($this);
            case \Dcp\Ui\RenderConfigManager::ViewMode:
                return new \Dcp\Search\html5\Report_html5_view_render($this);
        }

        return null;
    }
}

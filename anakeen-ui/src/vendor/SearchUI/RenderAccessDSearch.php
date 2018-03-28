<?php

namespace SearchUI;
use Dcp\Search\html5\Search_html5_edit_render;
use Dcp\Search\html5\Search_html5_view_render;

class DSearchAccess implements \Dcp\Ui\IRenderConfigAccess
{
    /**
     * @param string $mode
     * @return \Dcp\Ui\IRenderConfig
     */
    public function getRenderConfig($mode,  \Doc $document)
    {
        switch ($mode) {
            case \Dcp\Ui\RenderConfigManager::CreateMode:
            case \Dcp\Ui\RenderConfigManager::EditMode:
                return new Search_html5_edit_render($this);
            case \Dcp\Ui\RenderConfigManager::ViewMode:
                return new Search_html5_view_render($this);
        }

        return null;
    }
}

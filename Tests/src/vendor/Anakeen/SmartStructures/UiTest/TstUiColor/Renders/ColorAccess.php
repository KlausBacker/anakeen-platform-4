<?php

namespace Anakeen\SmartStructures\UiTest\TstUiColor\Renders;

class ColorAccess implements \Dcp\Ui\IRenderConfigAccess
{
    /**
     * @param string $mode
     * @param \Doc   $document
     * @return \Dcp\Ui\IRenderConfig
     */
    public function getRenderConfig($mode, \Doc $document)
    {
        switch ($mode) {
            case \Dcp\Ui\RenderConfigManager::CreateMode:
            case \Dcp\Ui\RenderConfigManager::EditMode:
                return new ColorEditRender();
            case \Dcp\Ui\RenderConfigManager::ViewMode:
                return new ColorViewRender();
        }
        return null;
    }
}

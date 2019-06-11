<?php

namespace Anakeen\SmartStructures\UiTest\TstUiColor\Renders;

class ColorAccess implements \Anakeen\Ui\IRenderConfigAccess
{
    /**
     * @param string $mode
     * @param \Anakeen\Core\Internal\SmartElement   $document
     * @return \Anakeen\Ui\IRenderConfig
     */
    public function getRenderConfig($mode, \Anakeen\Core\Internal\SmartElement $document)
    {
        switch ($mode) {
            case \Anakeen\Ui\RenderConfigManager::CreateMode:
            case \Anakeen\Ui\RenderConfigManager::EditMode:
                return new ColorEditRender();
            case \Anakeen\Ui\RenderConfigManager::ViewMode:
                return new ColorViewRender();
        }
        return null;
    }
}

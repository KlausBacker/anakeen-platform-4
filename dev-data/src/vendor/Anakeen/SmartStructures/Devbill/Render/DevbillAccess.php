<?php

namespace Anakeen\SmartStructures\Devbill\Render;

class DevbillAccess implements \Anakeen\Ui\IRenderConfigAccess
{
    /**
     * Choose good render from view or edit mode
     *
     * @param  string                              $mode
     * @param  \Anakeen\Core\Internal\SmartElement $element
     * @return \Anakeen\Ui\IRenderConfig
     */
    public function getRenderConfig($mode, \Anakeen\Core\Internal\SmartElement $element)
    {
        switch ($mode) {
            case \Anakeen\Ui\RenderConfigManager::CreateMode:
            case \Anakeen\Ui\RenderConfigManager::EditMode:
                return new DevbillEditRender();
            case \Anakeen\Ui\RenderConfigManager::ViewMode:
                return new DevbillViewRender();
        }
        return null;
    }
}

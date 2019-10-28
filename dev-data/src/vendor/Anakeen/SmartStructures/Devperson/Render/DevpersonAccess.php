<?php

namespace Anakeen\SmartStructures\Devperson\Render;

class DevpersonAccess implements \Anakeen\Ui\IRenderConfigAccess
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
                return new DevpersonEditRender();
            case \Anakeen\Ui\RenderConfigManager::ViewMode:
                return new DevpersonViewRender();
        }
        return null;
    }
}

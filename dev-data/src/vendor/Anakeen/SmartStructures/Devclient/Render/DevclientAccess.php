<?php

namespace Anakeen\SmartStructures\Devclient\Render;

class DevclientAccess implements \Anakeen\Ui\IRenderConfigAccess
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
                return new DevclientEditRender();
            case \Anakeen\Ui\RenderConfigManager::ViewMode:
                return new DevclientViewRender();
        }
        return null;
    }
}

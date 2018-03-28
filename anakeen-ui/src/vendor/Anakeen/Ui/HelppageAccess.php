<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Anakeen\Ui;

class HelppageAccess implements \Dcp\Ui\IRenderConfigAccess
{
    /**
     * @param string $mode
     * @return \Dcp\Ui\IRenderConfig
     */
    public function getRenderConfig($mode, \Doc $document)
    {
        switch ($mode) {
            case \Dcp\Ui\RenderConfigManager::CreateMode:
            case \Dcp\Ui\RenderConfigManager::EditMode:
                return new HelppageEditRender();
            case \Dcp\Ui\RenderConfigManager::ViewMode:
                return new HelppageViewRender();
        }
        return null;
    }
}

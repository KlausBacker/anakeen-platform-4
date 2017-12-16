<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Anakeen\Ui;

class SSearchAccess implements \Dcp\Ui\IRenderConfigAccess
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
                return new SSearchEditRender();
            case \Dcp\Ui\RenderConfigManager::ViewMode:
                return new SSearchViewRender();
        }
        return null;
    }
}

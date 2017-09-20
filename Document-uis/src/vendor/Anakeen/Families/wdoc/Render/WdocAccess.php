<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Anakeen\Ui;

class WdocAccess implements \Dcp\Ui\IRenderConfigAccess
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
                return new WdocEditRender();
            case \Dcp\Ui\RenderConfigManager::ViewMode:
                return new WdocViewRender();
        }
        return null;
    }
}

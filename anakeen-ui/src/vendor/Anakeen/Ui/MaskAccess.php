<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Anakeen\Ui;

class MaskAccess implements \Dcp\Ui\IRenderConfigAccess
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
                return new MaskEditRender();
            case \Dcp\Ui\RenderConfigManager::ViewMode:
                return new MaskViewRender();
        }
        return null;
    }
}

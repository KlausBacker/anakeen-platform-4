<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Anakeen\Ui;

class CvdocAccess implements \Dcp\Ui\IRenderConfigAccess
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
                return new CvdocEditRender();
            case \Dcp\Ui\RenderConfigManager::ViewMode:
                return new CvdocViewRender();
        }
        return null;
    }
}

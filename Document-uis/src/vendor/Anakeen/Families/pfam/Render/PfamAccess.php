<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Anakeen\Ui;

class PfamAccess implements \Dcp\Ui\IRenderConfigAccess
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
                return new PfamEditRender();
            case \Dcp\Ui\RenderConfigManager::ViewMode:
                return new PfamViewRender();
        }
        return null;
    }
}

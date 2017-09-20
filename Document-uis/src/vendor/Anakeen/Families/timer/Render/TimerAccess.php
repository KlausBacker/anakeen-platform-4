<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Anakeen\Ui;

class TimerAccess implements \Dcp\Ui\IRenderConfigAccess
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
                return new TimerEditRender();
            case \Dcp\Ui\RenderConfigManager::ViewMode:
                return new TimerViewRender();
        }
        return null;
    }
}

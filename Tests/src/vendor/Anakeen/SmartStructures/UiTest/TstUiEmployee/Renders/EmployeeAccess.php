<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Anakeen\SmartStructures\UiTest\TstUiEmployee\Renders;

class EmployeeAccess implements \Dcp\Ui\IRenderConfigAccess
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
                return new EmployeeEditRender();
            case \Dcp\Ui\RenderConfigManager::ViewMode:
                return new EmployeeViewRender();
        }
        return null;
    }
}

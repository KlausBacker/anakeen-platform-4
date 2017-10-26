<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Test\DdUi;

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
                return new EmployeeEditRender($this);
            case \Dcp\Ui\RenderConfigManager::ViewMode:
                return new EmployeeViewRender($this);
        }
        return null;
    }
}

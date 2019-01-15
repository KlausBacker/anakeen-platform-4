<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Anakeen\SmartStructures\Task\Render;

class TaskAccess implements \Dcp\Ui\IRenderConfigAccess
{
    /**
     * @param string                              $mode
     * @param \Anakeen\Core\Internal\SmartElement $document
     * @return TaskEditRender|TaskViewRender|null
     */
    public function getRenderConfig($mode, \Anakeen\Core\Internal\SmartElement $document)
    {
        switch ($mode) {
            case \Dcp\Ui\RenderConfigManager::CreateMode:
            case \Dcp\Ui\RenderConfigManager::EditMode:
                return new TaskEditRender();
            case \Dcp\Ui\RenderConfigManager::ViewMode:
                return new TaskViewRender();
        }
        return null;
    }
}

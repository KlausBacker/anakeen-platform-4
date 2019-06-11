<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Anakeen\SmartStructures\Task\Render;

class TaskAccess implements \Anakeen\Ui\IRenderConfigAccess
{
    /**
     * @param string                              $mode
     * @param \Anakeen\Core\Internal\SmartElement $document
     * @return TaskEditRender|TaskViewRender|null
     */
    public function getRenderConfig($mode, \Anakeen\Core\Internal\SmartElement $document)
    {
        switch ($mode) {
            case \Anakeen\Ui\RenderConfigManager::CreateMode:
            case \Anakeen\Ui\RenderConfigManager::EditMode:
                return new TaskEditRender();
            case \Anakeen\Ui\RenderConfigManager::ViewMode:
                return new TaskViewRender();
        }
        return null;
    }
}

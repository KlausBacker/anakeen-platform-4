<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Anakeen\SmartStructures\UiTest\TstUiShowEmpty\Renders;

class TstUiShowEmptyAccess implements \Anakeen\Ui\IRenderConfigAccess
{
    /**
     * @param string $mode
     * @return \Anakeen\Ui\IRenderConfig
     */
    public function getRenderConfig($mode, \Anakeen\Core\Internal\SmartElement $document)
    {
        switch ($mode) {
            case \Anakeen\Ui\RenderConfigManager::CreateMode:
            case \Anakeen\Ui\RenderConfigManager::EditMode:
                return new TstUiShowEmptyEditRender();
            case \Anakeen\Ui\RenderConfigManager::ViewMode:
                return new TstUiShowEmptyViewRender();
        }
        return null;
    }
}

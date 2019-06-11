<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Anakeen\SmartStructures\Wdoc\Render;

class WdocAccess implements \Anakeen\Ui\IRenderConfigAccess
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
                return new WdocEditRender();
            case \Anakeen\Ui\RenderConfigManager::ViewMode:
                return new WdocViewRender();
        }
        return null;
    }
}

<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Test\Ddui;

class AllType extends \Dcp\Family\Document implements \Dcp\Ui\IRenderConfigAccess
{
    /**
     * @param string $mode
     * @return \Dcp\Ui\IRenderConfig
     */
    public function getRenderConfig($mode, \Doc $document)
    {
        if ($mode === \Dcp\Ui\RenderConfigManager::EditMode || $mode === \Dcp\Ui\RenderConfigManager::CreateMode) {
            return new AllRenderConfigEdit($this);
        }
        if ($mode === \Dcp\Ui\RenderConfigManager::ViewMode) {
            return new AllRenderConfigView($this);
        }
        return null;
    }



}
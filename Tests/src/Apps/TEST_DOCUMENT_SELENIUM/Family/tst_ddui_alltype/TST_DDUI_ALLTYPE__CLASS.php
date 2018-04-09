<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Test\Ddui;

class AllType extends \Anakeen\SmartStructures\Document implements \Dcp\Ui\IRenderConfigAccess
{
    /**
     * @param string $mode
     * @return \Dcp\Ui\IRenderConfig
     */
    public function getRenderConfig($mode, \Doc $document)
    {
        if ($mode === \Dcp\Ui\RenderConfigManager::EditMode || $mode === \Dcp\Ui\RenderConfigManager::CreateMode) {
            return new AllRenderConfigEdit();
        }
        if ($mode === \Dcp\Ui\RenderConfigManager::ViewMode) {
            return new AllRenderConfigView();
        }
        return null;
    }

    public function validConstraint($value)
    {
        if (!empty($value) && $value >= 10) {
            return _("doit etre inférieur à 10");
        }
        return null;

    }

}
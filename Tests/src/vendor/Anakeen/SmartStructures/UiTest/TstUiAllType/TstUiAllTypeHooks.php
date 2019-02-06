<?php

namespace Anakeen\SmartStructures\UiTest\TstUiAllType;

class TstUiAllTypeHooks extends \Anakeen\SmartElement
 implements \Anakeen\Ui\IRenderConfigAccess
{
    /**
     * @param string $mode
     * @param \Anakeen\Core\Internal\SmartElement   $document
     * @return \Anakeen\Ui\IRenderConfig
     */
    public function getRenderConfig($mode, \Anakeen\Core\Internal\SmartElement $document)
    {
        if ($mode === \Anakeen\Ui\RenderConfigManager::EditMode || $mode === \Anakeen\Ui\RenderConfigManager::CreateMode) {
            return new Renders\AllRenderConfigEdit();
        }
        if ($mode === \Anakeen\Ui\RenderConfigManager::ViewMode) {
            return new Renders\AllRenderConfigView();
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

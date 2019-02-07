<?php

namespace Anakeen\SmartStructures\UiTest\TestRender;

use Anakeen\SmartHooks;
use \SmartStructure\Fields\Tst_render as MyAttr;

class TestRenderHooks extends \Anakeen\SmartElement implements \Anakeen\Ui\IRenderConfigAccess
{
    /**
     * @param string $mode
     * @param \Anakeen\Core\Internal\SmartElement   $document
     * @return \Anakeen\Ui\IRenderConfig
     */
    public function getRenderConfig($mode, \Anakeen\Core\Internal\SmartElement $document)
    {
        if ($mode === \Anakeen\Ui\RenderConfigManager::EditMode || $mode === \Anakeen\Ui\RenderConfigManager::CreateMode) {
            return new Renders\RenderConfigEdit();
        }
        if ($mode === \Anakeen\Ui\RenderConfigManager::ViewMode) {
            return new Renders\RenderConfigView();
        }
        return null;
    }


    public function getCustomTitle()
    {
        return sprintf("%04d %s", $this->getRawValue(MyAttr::tst_ref), $this->getRawValue(MyAttr::tst_title));
    }


    public function registerHooks()
    {
        parent::registerHooks();
        $this->getHooks()->addListener(SmartHooks::POSTSTORE, function () {
            if (!$this->name) {
                $this->setLogicalName(sprintf("TST_RENDER_%04d", $this->getRawValue(MyAttr::tst_ref)));
            }
        });
    }
}

<?php

namespace Anakeen\SmartStructures\UiTest\TestRender;

use Anakeen\SmartHooks;
use \SmartStructure\Fields\Tst_render as MyAttr;

class TestRenderHooks extends \Anakeen\SmartElement implements \Dcp\Ui\IRenderConfigAccess
{
    /**
     * @param string $mode
     * @param \Anakeen\Core\Internal\SmartElement   $document
     * @return \Dcp\Ui\IRenderConfig
     */
    public function getRenderConfig($mode, \Anakeen\Core\Internal\SmartElement $document)
    {
        if ($mode === \Dcp\Ui\RenderConfigManager::EditMode || $mode === \Dcp\Ui\RenderConfigManager::CreateMode) {
            return new Renders\RenderConfigEdit();
        }
        if ($mode === \Dcp\Ui\RenderConfigManager::ViewMode) {
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
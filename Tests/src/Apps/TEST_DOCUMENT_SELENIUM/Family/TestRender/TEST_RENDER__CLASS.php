<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Test\Ddui;

use \Dcp\AttributeIdentifiers\tst_render as MyAttr;
class Render extends \SmartStructure\Document implements \Dcp\Ui\IRenderConfigAccess
{
    /**
     * @param string $mode
     * @return \Dcp\Ui\IRenderConfig
     */
    public function getRenderConfig($mode, \Doc $document)
    {
        if ($mode === \Dcp\Ui\RenderConfigManager::EditMode || $mode === \Dcp\Ui\RenderConfigManager::CreateMode) {
            return new RenderConfigEdit();
        }
        if ($mode === \Dcp\Ui\RenderConfigManager::ViewMode) {
            return new RenderConfigView();
        }
        return null;
    }


    public function getCustomTitle()
    {
        return sprintf("%04d %s", $this->getRawValue(MyAttr::tst_ref), $this->getRawValue(MyAttr::tst_title));
    }

    public function postStore() {
        if (! $this->name) {
            $this->setLogicalName(sprintf("TST_RENDER_%04d",  $this->getRawValue(MyAttr::tst_ref)));
        }
    }

}
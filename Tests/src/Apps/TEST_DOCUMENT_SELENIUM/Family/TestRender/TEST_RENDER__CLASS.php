<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Test\Ddui;

use \Dcp\AttributeIdentifiers\tst_render as MyAttr;
class Render extends \Dcp\Family\Document implements \Dcp\Ui\IRenderConfigAccess
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

}
<?php

namespace Anakeen\SmartStructures\UiTest\TestRender\Renders;

use SmartStructure\Attributes\Tst_render as myAttributes;

class RenderConfigView extends \Dcp\Ui\DefaultView
{
    public function getLabel(\Doc $document = null)
    {
        return __METHOD__;
    }


    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);
        $options->frame(myAttributes::tst_fr_desc)->setTemplate('{{{attributes.tst_desc.htmlContent}}}')->setLabelPosition(\Dcp\Ui\CommonRenderOptions::nonePosition);

        $options->frame(myAttributes::tst_fr_config)->setTemplate('<div class="test-document" />')
            ->setLabelPosition(\Dcp\Ui\CommonRenderOptions::nonePosition);


        return $options;
    }

    public function getMenu(\Doc $document)
    {
        $menu = parent::getMenu($document);

        $menuItem = new \Dcp\Ui\ItemMenu(
            "openWindow",
            ___("Open in new window", "tst")
        );
        $menuItem->setBeforeContent('<div class="fa fa-external-link-square" />');
        $menuItem->setUrl("#action/tst:openWindow");
        $menu->appendElement($menuItem);

        $menuItem = new \Dcp\Ui\ItemMenu(
            "exportData",
            ___("Export Data", "tst")
        );
        $menuItem->setBeforeContent('<div class="fa fa-database" />');
        $menuItem->setTarget("_self");
        $menuItem->setUrl("?app=TEST_DOCUMENT_SELENIUM&action=EXPORTRENDER");


        $menu->appendElement($menuItem);


        return $menu;
    }

    public function getJsReferences(\Doc $document = null)
    {
        $js = parent::getJsReferences();
        $js["smartElement"] = \Dcp\Ui\UIGetAssetPath::getJSSmartElementPath();
        $js["familyTestRender"] = "TEST_DOCUMENT_SELENIUM/dist/family/TestRender.js";
        return $js;
    }
}

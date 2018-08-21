<?php

namespace Anakeen\SmartStructures\UiTest\TestRender\Renders;

use Dcp\Ui\BarMenu;
use Dcp\Ui\RenderOptions;
use Dcp\Ui\UIGetAssetPath;
use SmartStructure\Fields\Tst_render as myAttributes;

class RenderConfigView extends \Dcp\Ui\DefaultView
{
    public function getLabel(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        return __METHOD__;
    }


    public function getOptions(\Anakeen\Core\Internal\SmartElement $document):RenderOptions
    {
        $options = parent::getOptions($document);
        $options->frame(myAttributes::tst_fr_desc)->setTemplate('{{{attributes.tst_desc.htmlContent}}}')->setLabelPosition(\Dcp\Ui\CommonRenderOptions::nonePosition);

        $options->frame(myAttributes::tst_fr_config)->setTemplate('<div class="test-document" />')
            ->setLabelPosition(\Dcp\Ui\CommonRenderOptions::nonePosition);


        return $options;
    }

    public function getMenu(\Anakeen\Core\Internal\SmartElement $document):BarMenu
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

    public function getJsReferences(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        $js = parent::getJsReferences();
        $js["smartElement"] = \Dcp\Ui\UIGetAssetPath::getJSSmartElementWidgetPath(true);
        $path = UIGetAssetPath::getElementAssets("uiTest", UIGetAssetPath::isInDebug() ? "dev" : "legacy");
        $js["familyTestRender"] = $path["familyTestRender"]["js"];
        return $js;
    }
}

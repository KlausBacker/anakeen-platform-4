<?php

namespace Anakeen\SmartStructures\UiTest\TestRender\Renders;

use Anakeen\Ui\BarMenu;
use Anakeen\Ui\RenderOptions;
use Anakeen\Ui\UIGetAssetPath;
use SmartStructure\Fields\Tst_render as myAttributes;

class RenderConfigView extends \Anakeen\Ui\DefaultView
{
    public function getLabel(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        return __METHOD__;
    }


    public function getOptions(\Anakeen\Core\Internal\SmartElement $document):RenderOptions
    {
        $options = parent::getOptions($document);
        $options->frame(myAttributes::tst_fr_desc)->setTemplate('{{{attributes.tst_desc.htmlContent}}}')->setLabelPosition(\Anakeen\Ui\CommonRenderOptions::nonePosition);

        $options->frame(myAttributes::tst_fr_config)->setTemplate('<div class="test-document" />')
            ->setLabelPosition(\Anakeen\Ui\CommonRenderOptions::nonePosition);


        return $options;
    }

    public function getMenu(\Anakeen\Core\Internal\SmartElement $document):BarMenu
    {
        $menu = parent::getMenu($document);

        $menuItem = new \Anakeen\Ui\ItemMenu(
            "openWindow",
            ___("Open in new window", "tst")
        );
        $menuItem->setBeforeContent('<div class="fa fa-external-link-square" />');
        $menuItem->setUrl("#action/tst:openWindow");
        $menu->appendElement($menuItem);

        $menuItem = new \Anakeen\Ui\ItemMenu(
            "exportData",
            ___("Export Data", "tst")
        );
        $menuItem->setBeforeContent('<div class="fa fa-database" />');
        $menuItem->setTarget("_self");
        $menuItem->setUrl(sprintf("/api/v2/smart-elements/%s.xml", $document->initid));


        $menu->appendElement($menuItem);


        return $menu;
    }

    public function getJsReferences(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        $js = parent::getJsReferences();
        $js["smartElement"] = \Anakeen\Ui\UIGetAssetPath::getJSSmartElementWidgetPath(true);
        $path = UIGetAssetPath::getElementAssets("uiTest", UIGetAssetPath::isInDebug() ? "dev" : "prod");
        $js["familyTestRender"] = $path["familyTestRender"]["js"];
        return $js;
    }
}

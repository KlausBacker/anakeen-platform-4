<?php

namespace Anakeen\SmartStructures\Mask\Render;

use Anakeen\Ui\BarMenu;
use Anakeen\Ui\DefaultConfigViewRender;
use Anakeen\Ui\ItemMenu;
use Anakeen\Ui\UIGetAssetPath;

class MaskViewRender extends DefaultConfigViewRender
{
    public function getJsReferences(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        $js = parent::getJsReferences();
        $path = UIGetAssetPath::getElementAssets("smartStructures", UIGetAssetPath::isInDebug() ? "dev" : "prod");
        $js["mask"] = $path["Mask"]["js"];

        return $js;
    }

    public function getTemplates(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        $templates = parent::getTemplates($document);
        $templates["sections"]["content"] = array(
            "file" => __DIR__.'/MaskView.mustache'
        );
        return $templates;
    }

    public function getMenu(\Anakeen\Core\Internal\SmartElement $document): BarMenu
    {
        $myMenu = parent::getMenu($document);
        $alteredSfButton = new ItemMenu("alteredSf");
        $alteredSfButton->setTextLabel(___("Voir visibilités altérées", "maskUi"));
        $alteredSfButton->setUrl("#action/alteredSf");
        $myMenu->appendElement($alteredSfButton);

        return $myMenu;
    }
}

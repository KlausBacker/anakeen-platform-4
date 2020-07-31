<?php

namespace Anakeen\SmartStructures\RenderDescription\Render;

use Anakeen\Ui\BarMenu;
use Anakeen\Ui\DefaultConfigViewRender;
use Anakeen\Ui\ItemMenu;
use Anakeen\Ui\UIGetAssetPath;

class RenderDescriptionViewRender extends DefaultConfigViewRender
{
    public function getJsReferences(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        $js = parent::getJsReferences();
        $path = UIGetAssetPath::getElementAssets("smartStructures", UIGetAssetPath::isInDebug() ? "dev" : "prod");
        $js["renderDescriptionView"] = $path["RenderDescriptionView"]["js"];

        return $js;
    }


}

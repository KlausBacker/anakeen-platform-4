<?php

namespace Anakeen\SmartStructures\Mask\Render;

use Anakeen\Ui\DefaultConfigViewRender;
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
}

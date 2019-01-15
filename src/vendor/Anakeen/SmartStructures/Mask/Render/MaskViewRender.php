<?php

namespace Anakeen\SmartStructures\Mask\Render;

use Anakeen\Ui\DefaultConfigViewRender;
use Dcp\Ui\UIGetAssetPath;

class MaskViewRender extends DefaultConfigViewRender
{
    public function getJsReferences(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        $js = parent::getJsReferences();
        $path = UIGetAssetPath::getElementAssets("smartStructures", UIGetAssetPath::isInDebug() ? "dev" : "legacy");
        $js["dduiMask"] = $path["Mask"]["js"];

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

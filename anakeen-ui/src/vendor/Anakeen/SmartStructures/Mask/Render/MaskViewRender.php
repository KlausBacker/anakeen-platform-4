<?php

namespace Anakeen\SmartStructures\Mask\Render;

use Anakeen\Ui\DefaultConfigViewRender;

class MaskViewRender extends DefaultConfigViewRender
{
    /**
     * @param \Anakeen\Core\Internal\SmartElement $document Document instance
     *
     * @return \Dcp\Ui\RenderOptions
     */
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document)
    {
        $options = parent::getOptions($document);
        return $options;
    }

    public function getJsReferences(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        $js = parent::getJsReferences();
        $version = \Anakeen\Core\ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, "WVERSION");


        $js["dduiMask"] = 'uiAssets/Families/mask/prod/MaskView.js?ws='.$version;
        if (\Dcp\Ui\UIGetAssetPath::isInDebug()) {
            $js["dduiMask"] = 'uiAssets/Families/mask/debug/MaskView.js?ws='.$version;
        }

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

<?php
/*
 * @author Anakeen
 * @package DDUI
*/

namespace Anakeen\Ui;

class MaskViewRender extends DefaultConfigViewRender
{
    /**
     * @param \Doc $document Document instance
     *
     * @return \Dcp\Ui\RenderOptions
     */
    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);
        return $options;
    }

    public function getJsReferences(\Doc $document = null)
    {
        $js = parent::getJsReferences();
        $version = \Anakeen\Core\Internal\ApplicationParameterManager::getScopedParameterValue("WVERSION");


        $js["dduiMask"] = 'uiAssets/Families/mask/prod/MaskView.js?ws='.$version;
        if (\Dcp\Ui\UIGetAssetPath::isInDebug()) {
            $js["dduiMask"] = 'uiAssets/Families/mask/debug/MaskView.js?ws='.$version;
        }

        return $js;
    }


    public function getTemplates(\Doc $document = null)
    {
        $templates = parent::getTemplates($document);
        $templates["sections"]["content"] = array(
            "file" => __DIR__.'/MaskView.mustache'
        );
        return $templates;
    }
}

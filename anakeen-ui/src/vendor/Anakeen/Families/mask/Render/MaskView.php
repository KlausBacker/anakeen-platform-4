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
        $version = \ApplicationParameterManager::getScopedParameterValue("WVERSION");
        $js["dduiMask"] = "uiAssets/Families/mask/maskView.js?ws=" . $version;
        return $js;
    }
}

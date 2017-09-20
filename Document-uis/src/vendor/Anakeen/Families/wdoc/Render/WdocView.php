<?php
/*
 * @author Anakeen
 * @package DDUI
*/

namespace Anakeen\Ui;

use Dcp\AttributeIdentifiers\WDOC as myAttributes;

class WdocViewRender extends defaultConfigViewRender
{
    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);
        return $options;
    }

    public function getCssReferences(\Doc $document = null)
    {
        $css = parent::getCssReferences();
        $version = \ApplicationParameterManager::getScopedParameterValue("WVERSION");
        $css["dduiWvdoc"] = "DOCUMENT/Layout/Wvdoc/wdoc.css?ws=" . $version;
        return $css;
    }
}

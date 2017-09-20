<?php
/*
 * @author Anakeen
 * @package DDUI
*/

namespace Anakeen\Ui;

use \Dcp\AttributeIdentifiers\WDOC as myAttributes;

class WdocEditRender extends defaultConfigEditRender
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
        $css["dduiWvdoc"] = "DOCUMENT/Layout/Wdoc/wdoc.css?ws=" . $version;
        return $css;
    }
}

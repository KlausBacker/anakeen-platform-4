<?php
namespace Sample\BusinessApp\Renders;
use Dcp\AttributeIdentifiers\Ba_Laboratory as MyAttr;


class LaboratoryView extends CommonView
{

    public function getCssReferences(\Doc $document = null)
    {
        $css = parent::getCssReferences($document);
        $css[__CLASS__] = "BUSINESS_APP/Families/Laboratory/Renders/laboratory.css";
        return $css;
    }
    
    public function getJsReferences(\Doc $document = null)
    {
        $js = parent::getJsReferences($document);
        $js[__CLASS__] = "BUSINESS_APP/Families/Laboratory/Renders/laboratory.js";
        return $js;
    }
}

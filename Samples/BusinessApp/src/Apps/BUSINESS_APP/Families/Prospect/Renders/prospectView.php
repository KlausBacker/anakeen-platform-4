<?php
namespace Sample\BusinessApp\Renders;
use Dcp\AttributeIdentifiers\Ba_Prospect as MyAttr;


class ProspectView extends CommonView
{


    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);

        $options->frame(MyAttr::pr_fr_ident)->setResponsiveColumns(
            [
                ["number" => 2, "minWidth" => "70rem"  , "maxWidth" => "100rem"],
                ["number" => 3]
            ]);
        return $options;
    }

    public function getCssReferences(\Doc $document = null)
    {
        $css = parent::getCssReferences($document);
        $ws=\ApplicationParameterManager::getScopedParameterValue("WVERSION");
        $css[__CLASS__] = "BUSINESS_APP/Families/Prospect/Renders/prospect.css"."?ws=$ws";
        return $css;
    }
    
    public function getJsReferences(\Doc $document = null)
    {
        $js = parent::getJsReferences($document);
        $ws=\ApplicationParameterManager::getScopedParameterValue("WVERSION");
        $js[__CLASS__] = "BUSINESS_APP/Families/Prospect/Renders/prospect.js"."?ws=$ws";
        return $js;
    }
}

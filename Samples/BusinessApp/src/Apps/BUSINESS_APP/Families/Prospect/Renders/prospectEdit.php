<?php
namespace Sample\BusinessApp\Renders;

use Dcp\AttributeIdentifiers\Ba_Prospect as MyAttr;

class ProspectEdit extends CommonEdit
{

    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);

        $options->htmltext()->setLabelPosition(\Dcp\Ui\CommonRenderOptions::upPosition);
        $options->htmltext()->setToolbar(\dcp\Ui\HtmltextRenderOptions::basicToolbar);
        $options->text(MyAttr::pr_postalcode)->setMaxLength(5);
        $options->longtext(MyAttr::pr_question)->setMaxDisplayedLineNumber(20);


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

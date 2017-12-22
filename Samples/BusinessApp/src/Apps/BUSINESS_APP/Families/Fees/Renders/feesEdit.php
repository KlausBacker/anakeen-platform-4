<?php
namespace Sample\BusinessApp\Renders;
use Dcp\AttributeIdentifiers\BA_FEES as MyAttr;
class FeesEdit extends CommonEdit
{

    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);
        $options->commonOption('fee_exp_tax')->showEmptyContent("Non calculé");
        $options->date('fee_period')->setKendoDateConfiguration(array(
            "start" => "year",
            "depth" => "year",
            "format" => "MMMM yyyy"
        ));
        $options->htmltext()->setLabelPosition(\Dcp\Ui\CommonRenderOptions::upPosition);
        $options->htmltext()->setToolbar(\dcp\Ui\HtmltextRenderOptions::basicToolbar);
        $options->arrayAttribute(MyAttr::fee_t_all_exp)->setLabelPosition(\Dcp\ui\CommonRenderOptions::nonePosition);
        $options->arrayAttribute(MyAttr::fee_t_all_exp)->setTemplate(file_get_contents(__DIR__."/feesArray.mustache"));
        $options->arrayAttribute()->setRowMinDefault(1);


        return $options;
    }



    public function getCssReferences(\Doc $document = null)
    {
        $css = parent::getCssReferences($document);
        $ws=\ApplicationParameterManager::getScopedParameterValue("WVERSION");
        $css[__CLASS__] = "BUSINESS_APP/Families/Fees/Renders/fees.css"."?ws=$ws";
        return $css;
    }

    public function getJsReferences(\Doc $document = null)
    {
        $js = parent::getJsReferences($document);
        $ws=\ApplicationParameterManager::getScopedParameterValue("WVERSION");
        $js[__CLASS__] = "BUSINESS_APP/Families/Fees/Renders/fees.js"."?ws=$ws";;
        return $js;
    }
}

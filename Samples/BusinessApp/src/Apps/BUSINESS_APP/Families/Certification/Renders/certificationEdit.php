<?php

namespace Sample\BusinessApp\Renders;

use Dcp\AttributeIdentifiers\Ba_Certification as MyAttr;
use Dcp\Ui\EnumRenderOptions;

class CertificationEdit extends CommonEdit
{

    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);

        $options->htmltext()->setLabelPosition(\Dcp\Ui\CommonRenderOptions::upPosition);
        $options->htmltext()->setToolbar(\dcp\Ui\HtmltextRenderOptions::basicToolbar);
        $options->arrayAttribute()->setLabelPosition(\Dcp\ui\CommonRenderOptions::nonePosition);
        $options->arrayAttribute()->setRowMinDefault(1);


        $timeline = '<iframe class="timeline timeline--audit" src="?app=CSTB&action=BA_TIMELINEAUDIT" />';
        $options->frame(MyAttr::cert_fr_timeline)->setTemplate($timeline)->setLabelPosition(\Dcp\ui\CommonRenderOptions::nonePosition);

        $addButton = '{{{attribute.htmlContent}}} <a class="btn btn-primary">Rechercher</a>';
        $options->frame(MyAttr::cert_fr_rd)->setTemplate($addButton);

        $timeline = '<iframe class="timeline timeline--essai" src="?app=CSTB&action=BA_TIMELINEESSAI" />';
        $options->frame(MyAttr::cert_efr_timeline)->setTemplate($timeline)->setLabelPosition(\Dcp\ui\CommonRenderOptions::nonePosition);
        $addButton = '{{{attribute.htmlContent}}} <a class="btn btn-primary">Rechercher</a>';
        $options->frame(MyAttr::cert_efr_rd)->setTemplate($addButton);


        $timeline = '<iframe class="timeline timeline--comite" src="?app=CSTB&action=BA_TIMELINECOMITE" />';
        $options->frame(MyAttr::cert_cfr_timeline)->setTemplate($timeline)->setLabelPosition(\Dcp\ui\CommonRenderOptions::nonePosition);
        $addButton = '{{{attribute.htmlContent}}} <a class="btn btn-primary">Rechercher</a>';
        $options->frame(MyAttr::cert_cfr_rd)->setTemplate($addButton);


        $options->enum(MyAttr::cert_f1)->setDisplay(EnumRenderOptions::verticalDisplay);
        $options->enum(MyAttr::cert_f2)->setDisplay(EnumRenderOptions::verticalDisplay);
        $options->enum(MyAttr::cert_f3)->setDisplay(EnumRenderOptions::verticalDisplay);

        $options->int(MyAttr::cert_eesp)
            ->setKendoNumericConfiguration(
                [
                    "format" => "0 m²"
                ]
            );

        return $options;
    }


    public function getCssReferences(\Doc $document = null)
    {
        $css = parent::getCssReferences($document);
        $ws = \ApplicationParameterManager::getScopedParameterValue("WVERSION");
        $css[__CLASS__] = "BUSINESS_APP/Families/Certification/Renders/certification.css" . "?ws=$ws";
        return $css;
    }

    public function getJsReferences(\Doc $document = null)
    {
        $js = parent::getJsReferences($document);
        $ws = \ApplicationParameterManager::getScopedParameterValue("WVERSION");
        $js[__CLASS__] = "BUSINESS_APP/Families/Certification/Renders/certification.js" . "?ws=$ws";;
        return $js;
    }
}

<?php
namespace Sample\BusinessApp\Renders;

use Dcp\AttributeIdentifiers\Ba_Laboratory as MyAttr;

class LaboratoryEdit extends CommonEdit
{

    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);

        $options->htmltext()->setLabelPosition(\Dcp\Ui\CommonRenderOptions::upPosition);
        $options->htmltext()->setToolbar(\dcp\Ui\HtmltextRenderOptions::basicToolbar);

        return $options;
    }
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

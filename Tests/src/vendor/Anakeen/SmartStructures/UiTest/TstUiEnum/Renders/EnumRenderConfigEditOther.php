<?php

namespace Anakeen\SmartStructures\UiTest\TstUiEnum\Renders;

use SmartStructure\Attributes\Tst_ddui_enum as myAttributes;

class EnumRenderConfigEditOther extends EnumRenderConfigEdit
{
    public function getLabel(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        return "Enum Edit Other";
    }

    public function getOptions(\Anakeen\Core\Internal\SmartElement $document)
    {
        $options = parent::getOptions($document);

        $options->enum()->useOtherChoice(true);

        $tagTemplate='<span class="dcpAttribute__content--enum-single' .
            '#if (exists === false) { #'.
            ' dcpAttribute__content--enum-single--other'.
            '#}#'.
            '"> #: displayValue #</span>';
        $options->enum()->setOption("kendoMultiSelectConfiguration", array(
            "tagTemplate"=>$tagTemplate));
        $options->enum()->setOption("kendoDropDownConfiguration", array(
            "valueTemplate"=>$tagTemplate));

        return $options;
    }

    public function getCssReferences(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        $version = \Anakeen\Core\Internal\ApplicationParameterManager::getParameterValue("CORE", "WVERSION");
        $css = parent::getCssReferences($document);
        $css["tsteditverticalenum"] = "TEST_DOCUMENT_SELENIUM/Family/tst_ddui_enum/tstenumvertical.css?ws=".$version;
        return $css;
    }
}
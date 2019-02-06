<?php

namespace Anakeen\SmartStructures\UiTest\TstUiEnum\Renders;

use Anakeen\Core\ContextManager;
use Anakeen\Ui\RenderOptions;
use SmartStructure\Fields\Tst_ddui_enum as myAttributes;

class EnumRenderConfigEditDefault extends \Anakeen\Ui\DefaultEdit
{
    public function getLabel(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        return "Enum Edit Default";
    }

    public function getOptions(\Anakeen\Core\Internal\SmartElement $document):RenderOptions
    {
        $options = parent::getOptions($document);

        $options->document()->setTabPlacement(
            \Anakeen\Ui\DocumentRenderOptions::tabTopProportionalPlacement
        );
        return $options;
    }


    public function getCssReferences(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        $version =  ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, "WVERSION");
        $css = parent::getCssReferences($document);
        $css["tstotherenum"] = "TEST_DOCUMENT_SELENIUM/Layout/testOtherEnum.css?ws=".$version;
        return $css;
    }
}

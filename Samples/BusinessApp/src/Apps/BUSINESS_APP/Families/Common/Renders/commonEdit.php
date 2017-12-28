<?php
namespace Sample\BusinessApp\Renders;
class CommonEdit extends \Dcp\Ui\DefaultEdit
{

    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);

        // active native spell checker
        $options->htmltext()->setCkEditorConfiguration([
            "disableNativeSpellChecker" => false
        ]);
        $options->money()->setTemplate(file_get_contents(__DIR__.'/moneyAttribute.mustache'));

        return $options;
    }

    public function getJsReferences(\Doc $document = null)
    {
        $js = parent::getJsReferences($document);
        $js["ba-common"] = "BUSINESS_APP/Families/Common/Renders/common.js";
        return $js;
    }

    public function getCssReferences(\Doc $document = null)
    {
        $css = parent::getCssReferences($document);
        $css["ba-common"] = "./BUSINESS_APP/Families/Common/Renders/common.css";
        return $css;
    }

    public function getTemplates(\Doc $document = null) {
        $templates=parent::getTemplates($document);
        $templates["body"]["content"]="{{> header}}{{> menu}}{{> content}}{{> menu}}{{> footer}}";
        return $templates;
    }
}

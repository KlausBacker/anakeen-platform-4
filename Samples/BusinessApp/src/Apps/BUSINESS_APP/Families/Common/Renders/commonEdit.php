<?php
namespace Sample\BusinessApp\Renders;
class CommonEdit extends \Dcp\Ui\DefaultEdit
{
    use Common {
        Common::getOptions as getCommonOptions;
    }

    public function getOptions(\Doc $document)
    {
        $options = $this->getCommonOptions($document);

        // active native spell checker
        $options->htmltext()->setCkEditorConfiguration([
            "disableNativeSpellChecker" => false
        ]);

        return $options;
    }

    public function getJsReferences(\Doc $document = null)
    {
        $js = parent::getJsReferences($document);
        $js["cstb-common"] = "BUSINESS_APP/Families/Common/Renders/common.js";
        return $js;
    }
}

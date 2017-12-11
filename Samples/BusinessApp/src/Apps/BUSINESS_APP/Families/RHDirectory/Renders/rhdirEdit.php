<?php
namespace Sample\BusinessApp\Renders;
use Dcp\AttributeIdentifiers\BA_RHDIR as MyAttr;
class RHDirEdit extends CommonEdit
{

    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);
        $options->htmltext()->setLabelPosition(\Dcp\Ui\CommonRenderOptions::upPosition);
        $options->htmltext()->setToolbar(\dcp\Ui\HtmltextRenderOptions::basicToolbar);
        $options->arrayAttribute()->setRowMinDefault(1);
        return $options;
    }

    public function getJsReferences(\Doc $document = null)
    {
        $js = parent::getJsReferences($document);
        $ws=\ApplicationParameterManager::getScopedParameterValue("WVERSION");
        $js[__CLASS__] = "BUSINESS_APP/Families/RHDir/Renders/rhdir.js"."?ws=$ws";;
        return $js;
    }
}

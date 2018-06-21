<?php
namespace Anakeen\SmartStructures\Dsearch\Render;

use SmartStructure\Attributes\Dsearch as myAttr;
use Dcp\Ui\DefaultEdit;
use Dcp\Ui\ItemMenu as ItemMenu;

class SearchEditRender extends DefaultEdit
{

    public function getLabel(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        return "Search edit";
    }

    public function getTemplates(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        $templates = parent::getTemplates($document);
        $templates["sections"]["content"]["file"]
            = __DIR__ . "/searchHTML5_edit.mustache";

        return $templates;
    }

    public function getJsReferences(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        $js = parent::getJsReferences($document);

        $js["smartElementGrid"] = \Dcp\Ui\UIGetAssetPath::getJSSmartElementGridPath();
        $js["dSearch"] = \Dcp\Ui\UIGetAssetPath::getCustomAssetPath('uiAssets/Families/dsearch/prod/dsearch.js');
        if (\Dcp\Ui\UIGetAssetPath::isInDebug()) {
            $js["dSearch"] = \Dcp\Ui\UIGetAssetPath::getCustomAssetPath('uiAssets/Families/dsearch/debug/dsearch.js');
        }

        return $js;
    }

    public function getMenu(\Anakeen\Core\Internal\SmartElement $document)
    {
        $myMenu = parent::getMenu($document);
        $myItem = new ItemMenu("view", "");
        $myItem->setTextLabel(___("consult", "searchUi"));
        $myItem->setUrl("#action/previewEdit");
        $myMenu->appendElement($myItem);

        return $myMenu;
    }

    public function getOptions(\Anakeen\Core\Internal\SmartElement $document)
    {
        $options = parent::getOptions($document);

        $options->enum(myAttr::se_ol)->setDisplay(
            \Dcp\Ui\EnumRenderOptions::horizontalDisplay
        )->setLabelPosition(\Dcp\Ui\CommonRenderOptions::nonePosition);
        $options->enum(myAttr::se_ol)->useFirstChoice(true);
        $options->enum(myAttr::se_leftp)->setDisplay(
            \Dcp\Ui\EnumRenderOptions::boolDisplay
        );
        $options->enum(myAttr::se_rightp)->setDisplay(
            \Dcp\Ui\EnumRenderOptions::boolDisplay
        );
        $options->enum(myAttr::se_famonly)->setDisplay(
            \Dcp\Ui\EnumRenderOptions::boolDisplay
        );
        $options->enum(myAttr::se_trash)->setDisplay(
            \Dcp\Ui\EnumRenderOptions::horizontalDisplay
        );
        $options->enum(myAttr::se_acl)->setDisplay(
            \Dcp\Ui\EnumRenderOptions::horizontalDisplay
        );
        $options->enum(myAttr::se_sysfam)->setDisplay(
            \Dcp\Ui\EnumRenderOptions::boolDisplay
        );
        $options->enum(myAttr::se_ols)->useFirstChoice(true);

        $options->frame(myAttr::se_crit)->setOption("collapse", false);

        $options->text(myAttr::se_t_detail)->setLabelPosition("none");
        $options->enum(myAttr::se_leftp)->setAttributeLabel("(");
        $options->enum(myAttr::se_rightp)->setAttributeLabel(")");

        $options->frame(myAttr::se_crit)->setResponsiveColumns(array(
            [
                "number" => 2,
                "minWidth" => "60rem",
                "maxWidth" => "100rem",
                "grow" => true],
            [
                "number" => 3,
                "grow" => true
            ]
        ));

        return $options;
    }

    public function getVisibilities(\Anakeen\Core\Internal\SmartElement $document)
    {
        $visibilities = parent::getVisibilities($document);

        $visibilities->setVisibility(
            myAttr::se_key,
            \Dcp\Ui\RenderAttributeVisibilities::HiddenVisibility
        );
        $visibilities->setVisibility(
            myAttr::se_case,
            \Dcp\Ui\RenderAttributeVisibilities::HiddenVisibility
        );

        return $visibilities;
    }
}

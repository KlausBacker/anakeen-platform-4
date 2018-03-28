<?php
/**
 * Created by PhpStorm.
 * User: charles
 * Date: 15/04/15
 * Time: 09:36
 */

namespace Dcp\Search\html5;


use SmartStructure\Attributes\Dsearch as myAttr;
use Dcp\Ui\DefaultEdit;
use dcp\ui\MenuTargetOptions as MenuTargetOptions;
use dcp\ui\ItemMenu as ItemMenu;

class Search_html5_edit_render extends DefaultEdit
{

    public function getLabel(\Doc $document = null)
    {
        return "Search edit";
    }

    public function getTemplates(\Doc $document = null)
    {
        $templates = parent::getTemplates($document);
        $templates["sections"]["content"]["file"]
            = __DIR__ . "/searchHTML5_edit.mustache";

        return $templates;
    }

    public function getJsReferences(\Doc $document = null)
    {

        $js = parent::getJsReferences($document);

        $ws = \Dcp\UI\UIGetAssetPath::getWs();
        $js["smartElementGrid"] = \Dcp\UI\UIGetAssetPath::getJSSmartElementGridPath();
        $js["dSearch"] = 'uiAssets/Families/dsearch/prod/dsearch.js?ws=' . $ws;
        if (\Dcp\UI\UIGetAssetPath::isInDebug()) {
            $js["dSearch"] = 'uiAssets/Families/dsearch/debug/dsearch.js?ws=' . $ws;
        }

        return $js;
    }

    public function getMenu(\Doc $document)
    {
        $myMenu = parent::getMenu($document);
        $myItem = new ItemMenu("view", "");
        $myItem->setTextLabel(___("consult", "searchUi"));
        $myItem->setUrl("#action/previewEdit");
        $myMenu->appendElement($myItem);

        return $myMenu;
    }

    public function getOptions(\Doc $document)
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

    public function getVisibilities(\Doc $document)
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
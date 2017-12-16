<?php
/**
 * Created by PhpStorm.
 * User: charles
 * Date: 15/04/15
 * Time: 09:36
 */

namespace Dcp\Search\html5;


use Dcp\AttributeIdentifiers\Dsearch;
use Dcp\Ui\DefaultEdit;
use dcp\ui\MenuTargetOptions as MenuTargetOptions;
use dcp\ui\ItemMenu as ItemMenu;

class Search_html5_edit_render extends DefaultEdit
{

    public function getLabel(\Doc $document = null) {
        return "Search edit";
    }
    public function getTemplates(\Doc $document = null)
    {
        $templates = parent::getTemplates($document);
        $templates["sections"]["content"]["file"]
            = __DIR__."/searchHTML5_edit.mustache";
        return $templates;
    }

    public function getJsReferences(\Doc $document = null)
    {

        $js = parent::getJsReferences($document);

        $js["mySearchAttributeHelper"]
            = "uiAssets/Families/dsearch/searchAttributeHelper.js";
        $js["searchUIconstraints"] = "uiAssets/Families/dsearch/searchConstraints.js";
        $js["searchUI"] = "uiAssets/Families/dsearch/searchUI.js";
        $js["searchUIEventEdit"]
            = "uiAssets/Families/dsearch/searchUIEventEdit.js";
        $js["searchUICreationEvent"]
            = "uiAssets/Families/dsearch/searchUICreationEvent.js";

        $js["stickyTable"]="uiAssets/Families/dsearch/jquery.stickytableheaders.min.js";
        return $js;
    }

    public function getCssReferences(\Doc $document = null)
    {

        $css = parent::getJsReferences($document);

        $css["searchEditStyle"] = "uiAssets/Families/dsearch/searchRender.css";
        $css["docGridStyle"] = "DOCUMENT_GRID_HTML5/widgets/docGrid.css";

        return $css;
    }

    public function getMenu(\Doc $document)
    {
        $myMenu = parent::getMenu($document);
        $myItem = new ItemMenu("view", "");
        $myItem->setTextLabel(___("consult", "searchUi"));
        $myItem->setUrl("#action/previewEdit");
        $myMenu->appendElement($myItem);

        /*
        $createItem = $myMenu->getElement("create");
        if ($createItem) {
            $createItem->setUrl("#action/confirmCreation");
        }

        $createAndCloseItem = $myMenu->getElement("createAndClose");
        if ($createAndCloseItem) {
            $createAndCloseItem->setUrl("#action/confirmCreationClose");
        }*/

        return $myMenu;
    }

    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);

        $options->enum(Dsearch::se_ol)->setDisplay(
            \Dcp\Ui\EnumRenderOptions::horizontalDisplay
        )->setLabelPosition(\Dcp\Ui\CommonRenderOptions::nonePosition);
        $options->enum(Dsearch::se_ol)->useFirstChoice(true);
        $options->enum(Dsearch::se_leftp)->setDisplay(
            \Dcp\Ui\EnumRenderOptions::boolDisplay
        );
        $options->enum(Dsearch::se_rightp)->setDisplay(
            \Dcp\Ui\EnumRenderOptions::boolDisplay
        );
        $options->enum(Dsearch::se_famonly)->setDisplay(
            \Dcp\Ui\EnumRenderOptions::boolDisplay
        );
        $options->enum(Dsearch::se_trash)->setDisplay(
            \Dcp\Ui\EnumRenderOptions::horizontalDisplay
        );
        $options->enum(Dsearch::se_acl)->setDisplay(
            \Dcp\Ui\EnumRenderOptions::horizontalDisplay
        );
        $options->enum(Dsearch::se_sysfam)->setDisplay(
            \Dcp\Ui\EnumRenderOptions::boolDisplay
        );
        $options->enum(Dsearch::se_ols)->useFirstChoice(true);

        $options->frame(Dsearch::se_crit)->setOption("collapse", false);

        $options->text(Dsearch::se_t_detail)->setLabelPosition("none");
        $options->enum(Dsearch::se_leftp)->setAttributeLabel("(");
        $options->enum(Dsearch::se_rightp)->setAttributeLabel(")");

        return $options;
    }

    public function getVisibilities(\Doc $document)
    {
        $visibilities = parent::getVisibilities($document);

        $visibilities->setVisibility(
            Dsearch::se_key,
            \Dcp\Ui\RenderAttributeVisibilities::HiddenVisibility
        );
        $visibilities->setVisibility(
            Dsearch::se_case,
            \Dcp\Ui\RenderAttributeVisibilities::HiddenVisibility
        );

        return $visibilities;
    }

}
<?php
namespace Anakeen\SmartStructures\Dsearch\Render;

use Anakeen\Ui\BarMenu;
use Anakeen\Ui\RenderAttributeVisibilities;
use Anakeen\Ui\RenderOptions;
use Anakeen\Ui\UIGetAssetPath;
use SmartStructure\Fields\Dsearch as myAttr;
use Anakeen\Ui\DefaultEdit;
use Anakeen\Ui\ItemMenu as ItemMenu;

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
        $js["dSearch"] = UIGetAssetPath::getElementAssets("smartStructures", UIGetAssetPath::isInDebug() ? "dev": "prod")["Dsearch"]["js"];
        return $js;
    }


    public function getOptions(\Anakeen\Core\Internal\SmartElement $document):RenderOptions
    {
        $options = parent::getOptions($document);

        $options->enum(myAttr::se_ol)->setDisplay(
            \Anakeen\Ui\EnumRenderOptions::horizontalDisplay
        )->setLabelPosition(\Anakeen\Ui\CommonRenderOptions::nonePosition);
        $options->enum(myAttr::se_ol)->useFirstChoice(true);
        $options->enum(myAttr::se_leftp)->setDisplay(
            \Anakeen\Ui\EnumRenderOptions::boolDisplay
        );
        $options->enum(myAttr::se_rightp)->setDisplay(
            \Anakeen\Ui\EnumRenderOptions::boolDisplay
        );
        $options->enum(myAttr::se_famonly)->setDisplay(
            \Anakeen\Ui\EnumRenderOptions::boolDisplay
        );
        $options->enum(myAttr::se_trash)->setDisplay(
            \Anakeen\Ui\EnumRenderOptions::horizontalDisplay
        );
        $options->enum(myAttr::se_acl)->setDisplay(
            \Anakeen\Ui\EnumRenderOptions::horizontalDisplay
        );
        $options->enum(myAttr::se_sysfam)->setDisplay(
            \Anakeen\Ui\EnumRenderOptions::boolDisplay
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

    public function getVisibilities(\Anakeen\Core\Internal\SmartElement $document, \SmartStructure\Mask $mask = null) : RenderAttributeVisibilities
    {
        $visibilities = parent::getVisibilities($document, $mask);

        $visibilities->setVisibility(
            myAttr::se_key,
            \Anakeen\Ui\RenderAttributeVisibilities::HiddenVisibility
        );
        $visibilities->setVisibility(
            myAttr::se_case,
            \Anakeen\Ui\RenderAttributeVisibilities::HiddenVisibility
        );

        return $visibilities;
    }
}

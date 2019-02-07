<?php
/*
 * @author Anakeen
 * @package DDUI
*/

namespace Anakeen\SmartStructures\Igroup\Render;

use Anakeen\Ui\DefaultConfigViewRender;
use Anakeen\Ui\CommonRenderOptions;
use Anakeen\Ui\RenderAttributeVisibilities;
use Anakeen\Ui\RenderOptions;
use \SmartStructure\Fields\Igroup as myAttributes;

class IgroupViewRender extends DefaultConfigViewRender
{
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document):RenderOptions
    {
        $options = parent::getOptions($document);
        $break2 = "50rem";

        if (strlen($document->getRawValue(myAttributes::grp_mail)) < 200) {
            // Display 2 columns if mail address size is not too big
            $options->frame(myAttributes::grp_fr_ident)->setResponsiveColumns(
                [
                    ["number" => 2, "minWidth" => $break2]
                ]
            );
        }
        $options->frame(myAttributes::grp_fr_ident)->setCollapse(false)->setLabelPosition(CommonRenderOptions::nonePosition);

        $options->frame(myAttributes::grp_fr_intranet)->setResponsiveColumns(
            [
                ["number" => 2, "minWidth" => $break2]
            ]
        )->setCollapse(false)->setLabelPosition(CommonRenderOptions::nonePosition);

        $options->frame(myAttributes::grp_fr)->setCollapse(false)->setLabelPosition(CommonRenderOptions::nonePosition);
        $options->arrayAttribute(myAttributes::grp_roles)->showEmptyContent(
            \Anakeen\Core\Utils\Strings::xmlEncode(___("No roles", "smart igroup"))
        );


        return $options;
    }

    public function getVisibilities(\Anakeen\Core\Internal\SmartElement $document, \SmartStructure\Mask $mask = null) : RenderAttributeVisibilities
    {
        $vis = parent::getVisibilities($document, $mask);
        if ($document->getRawValue(myAttributes::grp_mail)) {
            $vis->setVisibility(myAttributes::grp_hasmail, RenderAttributeVisibilities::HiddenVisibility);
        }
        $vis->setVisibility(myAttributes::us_meid, RenderAttributeVisibilities::HiddenVisibility);
        return $vis;
    }
}

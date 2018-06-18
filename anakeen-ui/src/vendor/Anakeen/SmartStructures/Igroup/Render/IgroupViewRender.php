<?php
/*
 * @author Anakeen
 * @package DDUI
*/

namespace Anakeen\SmartStructures\Igroup\Render;

use Anakeen\Ui\DefaultConfigViewRender;
use Dcp\Ui\CommonRenderOptions;
use Dcp\Ui\RenderAttributeVisibilities;
use \SmartStructure\Attributes\Igroup as myAttributes;

class IgroupViewRender extends DefaultConfigViewRender
{
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document)
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
            xml_entity_encode(___("No roles", "smart igroup"))
        );


        return $options;
    }

    public function getVisibilities(\Anakeen\Core\Internal\SmartElement $document)
    {
        $vis = parent::getVisibilities($document);
        if ($document->getRawValue(myAttributes::grp_mail)) {
            $vis->setVisibility(myAttributes::grp_hasmail, RenderAttributeVisibilities::HiddenVisibility);
        }
        return $vis;
    }
}

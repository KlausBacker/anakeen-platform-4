<?php
/*
 * @author Anakeen
 * @package DDUI
*/

namespace Anakeen\SmartStructures\Igroup\Render;

use Anakeen\Ui\DefaultConfigViewRender;
use Dcp\Ui\CommonRenderOptions;
use \SmartStructure\Attributes\Igroup as myAttributes;

class IgroupViewRender extends DefaultConfigViewRender
{
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document)
    {
        $options = parent::getOptions($document);
        $break2 = "50rem";
        $break3 = "70rem";
        $options->frame(myAttributes::grp_fr_ident)->setResponsiveColumns(
            [
                ["number" => 2, "minWidth" => $break2, "maxWidth" => $break3],
                ["number" => 3, "minWidth" => $break3,  "grow" => false]
            ]
        )->setCollapse(false)->setLabelPosition(CommonRenderOptions::nonePosition);
        $options->frame(myAttributes::grp_fr_intranet)->setResponsiveColumns(
            [
                ["number" => 2, "minWidth" => $break2]
            ]
        )->setCollapse(false)->setLabelPosition(CommonRenderOptions::nonePosition);

        $options->frame(myAttributes::grp_fr)->setCollapse(false)->setLabelPosition(CommonRenderOptions::nonePosition);

        return $options;
    }
}

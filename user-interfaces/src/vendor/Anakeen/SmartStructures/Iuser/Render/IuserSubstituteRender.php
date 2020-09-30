<?php
/*
 * @author Anakeen
 * @package DDUI
*/

namespace Anakeen\SmartStructures\Iuser\Render;

use Anakeen\Core\Internal\SmartElement;
use Anakeen\Ui\RenderAttributeVisibilities;
use SmartStructure\Fields\Iuser as myAttributes;

class IuserSubstituteRender extends IuserEditRender
{
    /**
     * Display only the us_fr_substitute frame
     * @param SmartElement $smartElement
     * @param \SmartStructure\Mask|null $mask
     * @return \Anakeen\Ui\RenderAttributeVisibilities
     * @throws \Anakeen\Ui\Exception
     */
    public function getVisibilities(
        SmartElement $smartElement,
        \SmartStructure\Mask $mask = null
    ): RenderAttributeVisibilities {
        $visibilities = parent::getVisibilities($smartElement, $mask);

        $oas = $smartElement->getAttributes();
        foreach ($oas as $oa) {
            if ($oa->type === "frame" && $oa->id !== myAttributes::us_fr_substitute) {
                $visibilities->setVisibility(
                    $oa->id,
                    \Anakeen\Ui\RenderAttributeVisibilities::HiddenVisibility
                );
            }
        }
        return $visibilities;
    }
}

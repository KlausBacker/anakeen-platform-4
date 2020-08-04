<?php

namespace Anakeen\SmartStructures\RenderDescription\Render;

use Anakeen\Ui\BarMenu;
use Anakeen\Ui\DefaultConfigViewRender;
use Anakeen\Ui\EnumRenderOptions;
use Anakeen\Ui\ItemMenu;
use Anakeen\Ui\RenderAttributeVisibilities;
use Anakeen\Ui\RenderOptions;
use Anakeen\Ui\UIGetAssetPath;
use SmartStructure\Fields\Renderdescription as DescriptionFields;

class RenderDescriptionViewRender extends DefaultConfigViewRender
{

    public function getOptions(\Anakeen\Core\Internal\SmartElement $document): RenderOptions
    {
        $options = parent::getOptions($document);

        $options->arrayAttribute(DescriptionFields::rd_t_fields)->setTranspositionWidthLimit("70rem");

        return $options;
    }
    public function getJsReferences(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        $js = parent::getJsReferences();
        $path = UIGetAssetPath::getElementAssets("smartStructures", UIGetAssetPath::isInDebug() ? "dev" : "prod");
        $js["renderDescriptionView"] = $path["RenderDescriptionView"]["js"];

        return $js;
    }

    public function getVisibilities(
        \Anakeen\Core\Internal\SmartElement $document,
        \SmartStructure\Mask $mask = null
    ): RenderAttributeVisibilities {
        $visibilities = parent::getVisibilities($document, $mask);
        $visibilities->setVisibility(
            DescriptionFields::rd_field,
            RenderAttributeVisibilities::HiddenVisibility
        );
        return $visibilities;
    }
}

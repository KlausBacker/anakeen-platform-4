<?php

namespace Anakeen\Hub\SmartStructures\HubConfigurationGeneric\Render;

use Anakeen\Ui\RenderOptions;
use SmartStructure\Fields\Hubconfigurationgeneric as HubConfigurationGenericFields;

trait THubConfigurationGenericCommonRender
{
    public function addDescriptions(RenderOptions &$options)
    {
        $assetPathDescription = function ($type) {
            return "<p>Give the url of the $type asset. The url is relative to the public directory</p>".
            "<p>If manifest is given, you have to specify a public static method call.</p>";
        };

        $options->text(HubConfigurationGenericFields::hge_cssasset)
            ->setDescription($assetPathDescription("css"));
        $options->text(HubConfigurationGenericFields::hge_jsasset)
            ->setDescription($assetPathDescription("js"));
        return $options;
    }
}

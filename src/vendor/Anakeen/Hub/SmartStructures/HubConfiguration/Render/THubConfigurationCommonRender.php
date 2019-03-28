<?php

namespace Anakeen\Hub\SmartStructures\HubConfiguration\Render;

use Anakeen\Ui\CommonRenderOptions;
use Anakeen\Ui\RenderAttributeVisibilities;
use Anakeen\Ui\RenderOptions;
use Anakeen\Ui\UIGetAssetPath;
use SmartStructure\Fields\Hubconfiguration as HubConfigurationFields;

trait THubConfigurationCommonRender
{

    public function addDescriptions(RenderOptions &$options)
    {
        $options->account(HubConfigurationFields::hub_visibility_roles)->setDescription(
            "<p>Roles mandatory to display this component to the hub station</p>".
            "<p>User account must have <b>one of this roles</b> to display component</p>"
        )->showEmptyContent("No specific roles needed to display component");
        $options->account(HubConfigurationFields::hub_execution_roles)
            ->setDescription("<p>Roles required for operation of this component</p>".
                "<p>User account must have <b>each roles</b> to perform component</p>")
            ->showEmptyContent("No one operation roles required");


        $options->frame(HubConfigurationFields::hub_security_frame)->setResponsiveColumns(
            [
                ["number" => 2, "minWidth" => "60rem", "grow" => false],
            ]
        );
        $options->tab(HubConfigurationFields::hub_config)->setResponsiveColumns(
            [
                ["number" => 2, "minWidth" => "60rem"],
            ]
        );
        return $options;
    }
}

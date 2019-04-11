<?php

namespace Anakeen\Hub\SmartStructures\HubConfiguration\Render;

use Anakeen\Ui\RenderOptions;
use SmartStructure\Fields\Hubconfiguration as HubConfigurationFields;
use Anakeen\Ui\RenderAttributeVisibilities as RenderAttributeVisibilities;

trait THubConfigurationCommonRender
{

    public function addDescriptions(RenderOptions &$options)
    {
        $options->account(HubConfigurationFields::hub_activated)->setDescription(
            "<p>Sets the element activated by <b>default</b></p>"
        );
        $options->account(HubConfigurationFields::hub_activated_order)->setDescription(
            "<p>sets the <b>priority</b> of the elements among the <b>activated elements</b></p>"
        );
        $options->account(HubConfigurationFields::hub_order)->setDescription(
            "<p>Sets the position of the hub element from <b>left to right</b> / <b>top to bottom</b> in <b>ascending</b> order<p>"
        );
        $options->account(HubConfigurationFields::hub_visibility_roles)->setDescription(
            "<p>Roles mandatory to display the hub element to the hub station</p>".
            "<p>User account must have <b>one of these roles</b> to display the hub element</p>"
        )->showEmptyContent("No roles needed to display hub element");
        $options->account(HubConfigurationFields::hub_execution_roles)
            ->setDescription("<p>Roles required to operate this hub element</p>".
                "<p>User account must have <b>each role</b> to use the hub element API</p>")
            ->showEmptyContent("No technical roles required");


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

    public function getCommonVisibilities(RenderAttributeVisibilities $visibilities, \Anakeen\Core\Internal\SmartElement $document, $mask)
    {
        $visibilities->setVisibility(HubConfigurationFields::hub_selectable, RenderAttributeVisibilities::HiddenVisibility);
        if ($document->getRawValue(HubConfigurationFields::hub_selectable) !== "TRUE") {
            $visibilities->setVisibility(HubConfigurationFields::hub_activated, RenderAttributeVisibilities::HiddenVisibility);
        }
        if ($document->getRawValue(HubConfigurationFields::hub_activated) !== "TRUE") {
            $visibilities->setVisibility(HubConfigurationFields::hub_activated_order, RenderAttributeVisibilities::HiddenVisibility);
        }
        return $visibilities;
    }
}

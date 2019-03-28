<?php

namespace Anakeen\Hub\SmartStructures\HubInstanciation\Render;

use Anakeen\Ui\RenderOptions;
use SmartStructure\Fields\Hubinstanciation as HubInstanciationFields;

trait THubInstanciationCommonRender
{
    public function addDescriptions(RenderOptions &$options)
    {
        $options->account(HubInstanciationFields::hub_access_roles)
            ->setDescription(
                "<p>Mandatory roles to access to main page of this hub instance</p>" .
                "<p>User must have one of these roles described here to access to user interface</p>"
            );
        $options->account(HubInstanciationFields::hub_super_role)
            ->setDescription(
                "<p>Mandatory role to access to all elements of the interface. </p>" .
                "<p>All functionnalities are displayed when user has this role</p>"
            );

        return $options;
    }
}

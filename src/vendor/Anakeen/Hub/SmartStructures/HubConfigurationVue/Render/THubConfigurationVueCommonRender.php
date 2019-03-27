<?php

namespace Anakeen\Hub\SmartStructures\HubConfigurationVue\Render;

use Anakeen\Ui\RenderOptions;
use SmartStructure\Fields\Hubconfigurationvue as HubConfVueFields;

trait THubConfigurationVueCommonRender
{
    public function commonRenderOptions(RenderOptions &$options)
    {
        $options->text(HubConfVueFields::hub_vue_router_entry)
            ->setDescription(
                "<p>Sub route location to access to the component</p>" .
                "<p>The hub station use it with its <i>Router entry</i>. This sub route is concatenate with the main router entry </p>"
            );

        return $options;
    }
}

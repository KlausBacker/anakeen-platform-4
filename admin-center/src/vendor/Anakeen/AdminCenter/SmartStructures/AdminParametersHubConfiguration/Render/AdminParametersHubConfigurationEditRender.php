<?php


namespace Anakeen\AdminCenter\SmartStructures\AdminParametersHubConfiguration\Render;

use Anakeen\Core\Internal\SmartElement;
use Anakeen\Hub\SmartStructures\HubConfigurationGeneric\Render\HubConfigurationGenericEditRender;
use Anakeen\Ui\EnumRenderOptions;
use Anakeen\Ui\RenderAttributeVisibilities;
use Anakeen\Ui\RenderOptions;
use SmartStructure\Fields\Adminparametershubconfiguration as AdminParametersHubConfigurationFields;

class AdminParametersHubConfigurationEditRender extends HubConfigurationGenericEditRender
{
    use TAdminParametersHubConfigurationCommonRender;

    public function getOptions(SmartElement $document): RenderOptions
    {
        $options = parent::getOptions($document);

        $options->enum(AdminParametersHubConfigurationFields::admin_hub_configuration_global)->setDisplay(EnumRenderOptions::boolDisplay);
        $options->enum(AdminParametersHubConfigurationFields::admin_hub_configuration_user)->setDisplay(EnumRenderOptions::boolDisplay);

        return $options;
    }
}

<?php


namespace Anakeen\AdminCenter\Exchange;

use Anakeen\Core\SmartStructure\ExportConfiguration;
use Anakeen\Hub\Exchange\HubExportGenericComponent;
use SmartStructure\Fields\Adminparametershubconfiguration as ComponentParametersFields;

class HubExportAdminParameterComponent extends HubExportGenericComponent
{
    public static $nsUrl= ExportConfiguration::NSBASEURL . "hub-component-admin-parameters/1.0";
    protected $nsPrefix = "hubc-admin-parameters";


    protected function getParameters()
    {
        $parameters = parent::getParameters();

        $this->addField(
            ComponentParametersFields::admin_hub_configuration_global,
            "display-global-parameters",
            $parameters
        );
        $this->addField(
            ComponentParametersFields::admin_hub_configuration_user,
            "display-users-parameters",
            $parameters
        );
        $this->addField(ComponentParametersFields::admin_hub_configuration_account, "specific-user", $parameters);
        $this->addField(
            ComponentParametersFields::admin_hub_configuration_namespace,
            "parameters-namespace",
            $parameters
        );
        $this->addField(ComponentParametersFields::admin_hub_configuration_label, "sidebar-label", $parameters);
        $this->addField(ComponentParametersFields::admin_hub_configuration_icon, "sidebar-icon", $parameters);


        return $parameters;
    }
}

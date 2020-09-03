<?php


namespace Anakeen\AdminCenter\Exchange;

use Anakeen\Hub\Exchange\HubExportComponent;
use SmartStructure\Fields\Adminparametershubconfiguration as ComponentParametersFields;

class HubExportAdminParameterComponent extends HubExportComponent
{
    protected $mainTag = "component-admin-parameters";

    public function appendTo(\DOMElement $parent)
    {
        $node = parent::appendTo($parent);

        $node->appendChild($this->getParameters());
        return $node;
    }


    protected function getParameters()
    {
        $parameters = $this->cel("parameters");

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

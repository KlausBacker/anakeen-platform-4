<?php


namespace Anakeen\Hub\Exchange;

use Anakeen\Core\SmartStructure\ExportConfiguration;
use SmartStructure\Fields\Hubconfigurationidentity as ComponentIdentityFields;

class HubExportIdentityComponent extends HubExportComponent
{
    public static $nsUrl= ExportConfiguration::NSBASEURL . "hub-component-identity/1.0";
    protected $nsPrefix = "hubc-identity";



    protected function getParameters()
    {

        $parameters = $this->cel("parameters");


        $this->addField(ComponentIdentityFields::email_alterable, "alterable-email", $parameters);
        $this->addField(ComponentIdentityFields::password_alterable, "alterable-password", $parameters);


        return $parameters;
    }
}

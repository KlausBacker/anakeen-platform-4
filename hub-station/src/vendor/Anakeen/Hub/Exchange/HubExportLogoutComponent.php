<?php


namespace Anakeen\Hub\Exchange;

use Anakeen\Core\SmartStructure\ExportConfiguration;
use SmartStructure\Fields\Hubconfigurationlogout as ComponentLogoutFields;

class HubExportLogoutComponent extends HubExportComponent
{
    public static $nsUrl= ExportConfiguration::NSBASEURL . "hub-component-logout/1.0";
    protected $nsPrefix = "hubc-logout";



    protected function getParameters()
    {

        $parameters = $this->cel("parameters");


        $this->addField(ComponentLogoutFields::logout_title, "title", $parameters);


        return $parameters;
    }
}

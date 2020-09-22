<?php


namespace Anakeen\Hub\Exchange;

use Anakeen\Core\SmartStructure\ExportConfiguration;
use SmartStructure\Fields\Hubconfigurationlabel as ComponentLabelFields;

class HubExportLabelComponent extends HubExportComponent
{
    public static $nsUrl= ExportConfiguration::NSBASEURL . "hub-component-label/1.0";
    protected $nsPrefix = "hubc-label";



    protected function getParameters()
    {

        $parameters = $this->cel("parameters");


        $this->addField(ComponentLabelFields::label, "label", $parameters);
        $this->addField(ComponentLabelFields::extended_label, "extended-label", $parameters);


        return $parameters;
    }
}

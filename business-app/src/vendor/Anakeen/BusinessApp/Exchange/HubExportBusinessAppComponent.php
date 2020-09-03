<?php


namespace Anakeen\BusinessApp\Exchange;

use Anakeen\Exchange\ExportSearch;
use Anakeen\Hub\Exchange\HubExportComponent;
use Anakeen\Search\SearchElements;
use SmartStructure\Fields\Hubbusinessapp as ComponentBa;
use SmartStructure\Search;

class HubExportBusinessAppComponent extends HubExportComponent
{
    protected $mainTag = "component-business-app";

    public function appendTo(\DOMElement $parent)
    {
        $node = parent::appendTo($parent);

        $node->appendChild($this->getParameters());
        $node->appendChild($this->addCollections());
        return $node;
    }


    protected function getParameters()
    {

        $parameters = $this->cel("parameters");


        $this->addField(ComponentBa::hba_icon_image, "icon", $parameters);

        $this->addFieldArrayTwoColumns(
            ComponentBa::hba_title,
            "title",
            ComponentBa::hba_language,
            "lang",
            $parameters
        );


        $collections = $this->cel("collections", null, $parameters);

        $this->addField(ComponentBa::hba_collection, "collection", $collections);


        $welcome = $this->cel("welcome", null, $parameters);
        $welcome->setAttribute("activated", ($this->smartElement->getRawValue(ComponentBa::hba_welcome_option)==="YES")?"true":"false");
        $this->addField(ComponentBa::hba_welcome_title, "title", $welcome);

        $create = $this->cel("structures-creation", null, $welcome);
        $this->addField(ComponentBa::hba_structure, "structure", $create);


        $grids = $this->cel("grids", null, $welcome);
        $this->addField(ComponentBa::hba_grid_collection, "collection", $grids);


        return $parameters;
    }

    protected function addCollections() {

        $collections = $this->cel("collections-configuration");
        $cids=$this->smartElement->getMultipleRawValues(ComponentBa::hba_collection);

        $cids=array_merge($cids,$this->smartElement->getMultipleRawValues(ComponentBa::hba_grid_collection));


        $s=new SearchElements("SEARCH");
        $s->addFilter(sprintf("id in (%s)", implode(",", $cids)));
        $dl=$s->getResults();


        foreach ($dl as $search) {
            /** @var Search $search */
            $es=new ExportSearch($search);

            $collections->appendChild($es->getXmlNode($this->dom));
        }

        return $collections;

    }
}

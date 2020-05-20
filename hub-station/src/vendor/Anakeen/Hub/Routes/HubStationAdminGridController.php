<?php

namespace Anakeen\Hub\Routes;

use Anakeen\Components\Grid\DefaultGridController;
use Anakeen\Components\Grid\Operators;
use Anakeen\Components\Grid\SmartGridBuilder;
use Anakeen\Components\Grid\SmartGridConfigBuilder;
use Anakeen\Components\Grid\SmartGridContentBuilder;
use Anakeen\Core\SEManager;
use SmartStructure\Fields\Hubconfiguration as HubconfigurationFields;
use SmartStructure\Hubconfiguration;

class HubStationAdminGridController extends DefaultGridController
{

    protected static function setCollectionId(SmartGridBuilder $builder, $collectionId, $clientConfig)
    {
        $builder->setCollection(Hubconfiguration::familyName);
    }

    protected static function setColumns(SmartGridBuilder $builder, $collectionId, $clientConfig)
    {
        $builder->addAbstract("key", [
            "dataFunction" => function ($se) {
                return "";
            }
        ]);
        $builder->addProperty("title");
        $builder->addField(HubconfigurationFields::hub_docker_position, [
            "hidden" => true
        ]);

        $builder->addField(HubConfigurationFields::hub_order, [
            "hidden" => true
        ]);
        $builder->addAbstract("hub_type", [
            "smartType" => "text",
            "title" => "Type",
            "sortable" => true,
            "filterable" => true,
            "dataFunction" => function ($se) {
                $doc = SEManager::getDocument($se->initid)->fromid;
                $fam = SEManager::getFamily($doc);
                return array("value" => $fam->getTitle(), "displayValue" => $fam->getTitle());
            }
        ]);
    }

    protected static function setCurrentContentPage(
        SmartGridContentBuilder $contentBuilder,
        $collectionId,
        $clientConfig
    ) {
        parent::setCurrentContentPage($contentBuilder, $collectionId, $clientConfig);
        $contentBuilder->setPageSize("ALL");
    }

    protected static function setContentSort(SmartGridContentBuilder $contentBuilder, $collectionId, $clientConfig)
    {
        parent::setContentSort($contentBuilder, $collectionId, $clientConfig);
        $contentBuilder->addSort("hub_order", "asc");
    }

    protected static function setContentFilter(SmartGridContentBuilder $contentBuilder, $collectionId, $clientConfig)
    {
        parent::setContentFilter($contentBuilder, $collectionId, $clientConfig);
        $contentBuilder->getSearch()->addFilter("%s = '%s'", HubconfigurationFields::hub_station_id, SEManager::getDocument($collectionId)->initid);
    }
}

<?php

namespace Anakeen\SmartStructures\Dsearch\Render;

use Anakeen\Components\Grid\DefaultGridController;
use Anakeen\Components\Grid\SmartGridConfigBuilder;
use Anakeen\Components\Grid\SmartGridContentBuilder;
use Anakeen\Core\SEManager;
use SmartStructure\Fields\Report;

class ReportGridController extends DefaultGridController
{
    protected static $collectionId;

    public static function getGridConfig($collectionId, $clientConfig)
    {
        $configBuilder = new SmartGridConfigBuilder();
        if (isset($collectionId)) {
            $configBuilder->setCollection($collectionId);
        }
        if (isset($clientConfig["pageable"])) {
            $configBuilder->setPageable($clientConfig["pageable"]);
        }
        $configBuilder->useDefaultColumns();
        $configBuilder->addRowAction(array("action"=> "display", "title"=> "Display"));
        $config = $configBuilder->getConfig();
        return $config;
    }
    public static function getGridContent($collectionId, $clientConfig)
    {
        $contentBuilder = new SmartGridContentBuilder();
        $config = self::getGridConfig($collectionId, $clientConfig);
        foreach ($config["columns"] as $column) {
            $contentBuilder->addColumn($column);
        }
        if (isset($collectionId)) {
            $contentBuilder->setCollection($collectionId);
        }
        if (isset($clientConfig["pageable"]["pageSize"])) {
            $contentBuilder->setPageSize($clientConfig["pageable"]["pageSize"]);
        }
        if (isset($clientConfig["page"])) {
            $contentBuilder->setPage($clientConfig["page"]);
        }

        if (isset($clientConfig["filter"])) {
            $contentBuilder->addFilter($clientConfig["filter"]);
        }
        if (isset($clientConfig["sort"])) {
            foreach ($clientConfig["sort"] as $sort) {
                $contentBuilder->addSort($sort["field"], $sort["dir"]);
            }
        } else {
            $report=SEManager::getDocument($collectionId);
            if ($report) {
                $sortOrderDir = $report->getRawValue(Report::rep_ordersort, "asc");
                $sortField = $report->getRawValue(Report::rep_idsort, "title");
                $contentBuilder->addSort($sortField, $sortOrderDir);
            }
        }
        return $contentBuilder->getContent();
    }

    public static function exportGridContent($response, $collectionId, $clientConfig)
    {
        self::$collectionId = $collectionId;
        return parent::exportGridContent($response, $collectionId, $clientConfig);
    }

    protected static function getContentBuilder(): SmartGridContentBuilder
    {
        $builder=parent::getContentBuilder();
        $report=SEManager::getDocument(self::$collectionId);
        if ($report) {
            $sortOrderDir = $report->getRawValue(Report::rep_ordersort, "asc");
            $sortField = $report->getRawValue(Report::rep_idsort, "title");
            $builder->setCollection($report->id);
            static::$collectionInitialized = true;
            $builder->addSort($sortField, $sortOrderDir);
        }
        return $builder;
    }
}

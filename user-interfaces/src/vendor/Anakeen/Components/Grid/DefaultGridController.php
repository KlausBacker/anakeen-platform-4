<?php

namespace Anakeen\Components\Grid;

use Anakeen\Router\Exception;
use Anakeen\Routes\Ui\Transaction\TransactionManager;

class DefaultGridController implements SmartGridController
{
    public static function getGridConfig($collectionId, $clientConfig)
    {
        $configBuilder = new SmartGridConfigBuilder();
        if (isset($collectionId)) {
            $configBuilder->setCollection($collectionId);
        }
        if (isset($clientConfig["pageable"])) {
            $configBuilder->setPageable($clientConfig["pageable"]);
        }
        if (isset($clientConfig["columns"])) {
            $configBuilder->setColumns($clientConfig["columns"]);
        } else {
            // use default columns for the collection
            $configBuilder->useDefaultColumns();
        }
        if (isset($clientConfig["actions"])) {
            foreach ($clientConfig["actions"] as $action) {
                $configBuilder->addRowAction($action);
            }
        }

        $config = $configBuilder->getConfig();
        return $config;
    }

    public static function getGridContent($collectionId, $clientConfig)
    {
        $contentBuilder = new SmartGridContentBuilder();
        if (isset($collectionId)) {
            $contentBuilder->setCollection($collectionId);
        }
        if (isset($clientConfig["pageable"]["pageSize"])) {
            $contentBuilder->setPageSize($clientConfig["pageable"]["pageSize"]);
        }
        if (isset($clientConfig["page"])) {
            $contentBuilder->setPage($clientConfig["page"]);
        }
        if (isset($clientConfig["columns"])) {
            foreach ($clientConfig["columns"] as $column) {
                $contentBuilder->addColumn($column);
            }
        }
        if (isset($clientConfig["filter"])) {
            $filterLogic = $clientConfig["filter"]["logic"];
            $filters = $clientConfig["filter"]["filters"];
            $contentBuilder->addFilter($clientConfig["filter"]);
        }
        if (isset($clientConfig["sort"])) {
            foreach ($clientConfig["sort"] as $sort) {
                $contentBuilder->addSort($sort["field"], $sort["dir"]);
            }
        }
        return $contentBuilder->getContent();
    }

    public static function exportGridContent($response, $collectionId, $clientConfig)
    {
        $exportBuilder = new SmartGridExport();
        $gridConfig = static::getGridConfig($collectionId, $clientConfig);
        $columns = $gridConfig["columns"];
        $contentBuilder = new SmartGridContentBuilder();
        if (isset($collectionId)) {
            $contentBuilder->setCollection($collectionId);
        }
        if (isset($clientConfig["pageable"]["pageSize"])) {
            $contentBuilder->setPageSize("ALL");
        }
        if (isset($exportBuilder->clientColumnsConfig)) {
            foreach ($columns as $column) {
                $contentBuilder->addColumn($column);
            }
        }
        $data = $contentBuilder->getContent();
        $exportBuilder->clientColumnsConfig = $columns;
        if (isset($clientConfig["onlySelection"]) && $clientConfig["onlySelection"]) {
            $exportBuilder->onlySelect = true;
            $exportBuilder->selectedRows = isset($clientConfig["selectedRows"]) ? $clientConfig["selectedRows"] : [];
        }
        $exportBuilder->unselectedRows = isset($clientConfig["unselectedRows"]) ? $clientConfig["unselectedRows"] : [];
        $transactionId = isset($clientConfig["transaction"]) ? $clientConfig["transaction"]["transactionId"] : null;
        if (isset($transactionId)) {
            if (empty($transactionId)) {
                $exception = new Exception("TRANS0002");
                $exception->setHttpStatus("400", "Transaction id missing");
                throw $exception;
            }
            return TransactionManager::runTransaction($transactionId, function ($tId) use ($response, $exportBuilder, $data) {
                $exportBuilder->transactionId = $tId;
                return $exportBuilder->doExport($response, $data);
            });
        } else {
            $transaction = TransactionManager::createTransaction();
            return $transaction->getData();
        }
    }
}

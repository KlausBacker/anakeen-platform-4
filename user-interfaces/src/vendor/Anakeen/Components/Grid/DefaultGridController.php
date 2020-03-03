<?php

namespace Anakeen\Components\Grid;

use Anakeen\Router\Exception;
use Anakeen\Routes\Ui\Transaction\TransactionManager;

class DefaultGridController implements SmartGridController
{
    /**
     * Get the Smart Element Grid configuration builder
     * @return SmartGridConfigBuilder
     */
    protected static function getConfigBuilder(): SmartGridConfigBuilder
    {
        return new SmartGridConfigBuilder();
    }

    /**
     * Get the Smart Element Grid content builder
     * @return SmartGridContentBuilder
     */
    protected static function getContentBuilder(): SmartGridContentBuilder
    {
        return new SmartGridContentBuilder();
    }

    /**
     * Set the config or content builder collection id
     * @param SmartGridBuilder $builder
     * @param $collectionId
     * @param $clientConfig
     */
    protected static function setCollectionId(SmartGridBuilder $builder, $collectionId, $clientConfig)
    {
        if (isset($collectionId)) {
            $builder->setCollection($collectionId);
        }
    }

    /**
     * Set the page configuration
     * @param SmartGridBuilder $builder
     * @param $collectionId
     * @param $clientConfig
     */
    protected static function setPageable(SmartGridBuilder $builder, $collectionId, $clientConfig)
    {
        if (isset($clientConfig["pageable"])) {
            $builder->setPageable($clientConfig["pageable"]);
        }
    }

    /**
     * Set the columns
     * @param SmartGridBuilder $builder
     * @param $collectionId
     * @param $clientConfig
     */
    protected static function setColumns(SmartGridBuilder $builder, $collectionId, $clientConfig)
    {
        if (isset($clientConfig["columns"])) {
            $builder->setColumns($clientConfig["columns"]);
        } else {
            if (is_a($builder, SmartGridConfigBuilder::class)) {
                // use default columns for the collection
                $builder->useDefaultColumns();
            } elseif (is_a($builder, SmartGridContentBuilder::class)) {
                $config = self::getGridConfig($collectionId, $clientConfig);
                if (isset($config["columns"])) {
                    $builder->setColumns($config["columns"]);
                }
            }
        }
    }

    /**
     * Set actions to configuration builder
     * @param SmartGridConfigBuilder $builder
     * @param $collectionId
     * @param $clientConfig
     */
    protected static function setActions(SmartGridConfigBuilder $builder, $collectionId, $clientConfig)
    {
        if (isset($clientConfig["actions"])) {
            foreach ($clientConfig["actions"] as $action) {
                $builder->addRowAction($action);
            }
        }
    }

    /**
     * Set current page to content
     * @param SmartGridContentBuilder $contentBuilder
     * @param $collectionId
     * @param $clientConfig
     */
    protected static function setCurrentContentPage(
        SmartGridContentBuilder $contentBuilder,
        $collectionId,
        $clientConfig
    ) {
        if (isset($clientConfig["page"])) {
            $contentBuilder->setPage($clientConfig["page"]);
        }
    }

    /**
     * Set filter to content
     * @param SmartGridContentBuilder $contentBuilder
     * @param $collectionId
     * @param $clientConfig
     */
    protected static function setContentFilter(SmartGridContentBuilder $contentBuilder, $collectionId, $clientConfig)
    {
        if (isset($clientConfig["filter"])) {
            $contentBuilder->addFilter($clientConfig["filter"]);
        }
    }

    /**
     * Set sort to content
     * @param SmartGridContentBuilder $contentBuilder
     * @param $collectionId
     * @param $clientConfig
     */
    protected static function setContentSort(SmartGridContentBuilder $contentBuilder, $collectionId, $clientConfig)
    {
        if (isset($clientConfig["sort"])) {
            foreach ($clientConfig["sort"] as $sort) {
                $contentBuilder->addSort($sort["field"], $sort["dir"]);
            }
        }
    }

    /**
     * Get the Smart Element Grid configuration
     * @param $collectionId
     * @param $clientConfig
     * @return array
     */
    public static function getGridConfig($collectionId, $clientConfig)
    {
        $configBuilder = static::getConfigBuilder();
        static::setCollectionId($configBuilder, $collectionId, $clientConfig);
        static::setPageable($configBuilder, $collectionId, $clientConfig);
        static::setColumns($configBuilder, $collectionId, $clientConfig);
        static::setActions($configBuilder, $collectionId, $clientConfig);
        return $configBuilder->getConfig();
    }


    /**
     * Get the Smart Element Grid content
     * @param $collectionId
     * @param $clientConfig
     * @return array
     */
    public static function getGridContent($collectionId, $clientConfig)
    {
        $contentBuilder = static::getContentBuilder();
        static::setCollectionId($contentBuilder, $collectionId, $clientConfig);
        static::setPageable($contentBuilder, $collectionId, $clientConfig);
        static::setCurrentContentPage($contentBuilder, $collectionId, $clientConfig);
        static::setColumns($contentBuilder, $collectionId, $clientConfig);
        static::setContentFilter($contentBuilder, $collectionId, $clientConfig);
        static::setContentSort($contentBuilder, $collectionId, $clientConfig);
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
            return TransactionManager::runTransaction(
                $transactionId,
                function ($tId) use ($response, $exportBuilder, $data) {
                    $exportBuilder->transactionId = $tId;
                    return $exportBuilder->doExport($response, $data);
                }
            );
        } else {
            $transaction = TransactionManager::createTransaction();
            return json_encode($transaction->getData());
        }
    }
}

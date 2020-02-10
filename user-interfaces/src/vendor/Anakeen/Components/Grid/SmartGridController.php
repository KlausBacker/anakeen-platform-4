<?php

namespace Anakeen\Components\Grid;

interface SmartGridController
{
    public static function getGridConfig($collectionId, $clientConfig);
    public static function getGridContent($collectionId, $clientConfig);
    public static function exportGridContent($response, $collectionId, $clientConfig);
}

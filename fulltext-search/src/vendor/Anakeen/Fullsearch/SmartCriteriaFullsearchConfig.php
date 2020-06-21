<?php


namespace Anakeen\Fullsearch;

use Anakeen\SmartCriteria\SmartCriteriaConfig;

class SmartCriteriaFullsearchConfig
{
    public static function getSmartCriteriaFilterConfiguration()
    {
        return array(
            "class" => \Anakeen\Fullsearch\FilterMatch::class,
            "operands" => [SmartCriteriaConfig::FIELD, SmartCriteriaConfig::VALUE],
            "availableOptions" => [],
            "labels" => [
                0 => "fulltext label"
            ]
        );
    }
}

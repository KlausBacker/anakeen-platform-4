<?php


namespace Anakeen\Fullsearch\Components\FulltextSmartCriteria\Routes;

use Anakeen\Components\SmartCriteria\Routes\SmartCriteriaConfigurationLoader;

class FulltextSmartCriteriaConfigurationLoader extends SmartCriteriaConfigurationLoader
{

    protected function completeCustomCriteria(&$criteria)
    {
        if ($criteria["kind"] === "fulltext") {
            if (!array_key_exists("label", $criteria)) {
                $criteria["label"] = "Recherche générale";
            }
        }
    }
}

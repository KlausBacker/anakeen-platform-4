<?php

namespace Anakeen\Fullsearch\Components\Grid;

use Anakeen\Components\Grid\DefaultGridController;
use Anakeen\Fullsearch\FilterMatch;

class DefaultGridFulltextController extends DefaultGridController
{

    /**
     * Set a fulltext search on the Smart Element Grid content
     * @param $contentBuilder
     * @param $collectionId
     * @param $clientConfig
     */
    public static function setFulltextSearch($contentBuilder, $collectionId, $clientConfig)
    {
        if (isset($clientConfig["fulltextSearch"]["searchDomain"])) {
            $domain = $clientConfig["fulltextSearch"]["searchDomain"];
            $search = $clientConfig["fulltextSearch"]["searchPattern"] ?: "";
            $contentBuilder->getSearch()->addFilter(new FilterMatch($domain, $search));
        }
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
        static::setFulltextSearch($contentBuilder, $collectionId, $clientConfig);
        static::setContentSort($contentBuilder, $collectionId, $clientConfig);
        return $contentBuilder->getContent();
    }
}
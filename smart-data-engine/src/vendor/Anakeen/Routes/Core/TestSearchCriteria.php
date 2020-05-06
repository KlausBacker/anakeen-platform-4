<?php

namespace Anakeen\Routes\Core;

use Anakeen\Router\ApiV2Response;
use Anakeen\Search\SearchCriteria\SearchCriteria;

/**
 * Class DocumentHistory
 *
 * @note    Used by route : POST /api/v2/searchcriteria/test/
 * @package Anakeen\Routes\Core
 */
class TestSearchCriteria
{
    protected $rawCriteriaData;

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($request, $args);

        return ApiV2Response::withData($response, $this->doRequest());
    }

    protected function initParameters(\Slim\Http\request $request, $args)
    {
        $this->rawCriteriaData = $request->getParsedBody();
    }

    public function doRequest()
    {
        $s = new \Anakeen\Search\SearchElements("DEVBILL");
        $s->addFilter(new SearchCriteria($this->rawCriteriaData));
        return array($s->search()->getResults(), $s->getSearchInfo());
    }
}

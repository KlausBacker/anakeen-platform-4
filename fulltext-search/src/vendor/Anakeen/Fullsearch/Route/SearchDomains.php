<?php

namespace Anakeen\Fullsearch\Route;

use Anakeen\Fullsearch\SearchDomainManager;
use Anakeen\Router\ApiV2Response;

class SearchDomains
{

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {


        $data = $this->doRequest();
        return ApiV2Response::withData($response, $data);
    }

    protected function doRequest()
    {
        $data = [];

        $data["config"]=array_values(SearchDomainManager::getConfig());

        return $data;
    }
}

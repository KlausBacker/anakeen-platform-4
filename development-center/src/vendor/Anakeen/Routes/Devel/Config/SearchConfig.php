<?php

namespace Anakeen\Routes\Devel\Config;

use Anakeen\Core\SEManager;
use Anakeen\Exchange\ExportSearch;
use Anakeen\Router\Exception;
use SmartStructure\Search;

/**
 * Get configuration of smart structure object
 * use by route GET /api/v2/devel/config/smart/searches/{search}.xml
 */
class SearchConfig
{
    /**
     * @var Search $search
     */
    protected $search;
    protected $searchId = 0;
    protected $type = "structures";

    /**
     * Return right accesses for a profil element
     *
     * @param \Slim\Http\request $request
     * @param \Slim\Http\response $response
     * @param $args
     *
     * @return \Slim\Http\response $response
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($request, $args);

        $response = $response->withAddedHeader("Content-type", "text/xml");
        $response = $response->write($this->doRequest());
        return $response;
    }


    protected function initParameters(\Slim\Http\request $request, $args)
    {
        $this->searchId = $args["search"];
        $this->search = SEManager::getDocument($this->searchId);
        if (!$this->search) {
            throw new Exception(sprintf("Search \"%s\" not found", $this->searchId));
        }
        if (!is_a($this->search, Search::class)) {
            throw new Exception(sprintf("Search \"%s\" is not a search", $this->searchId));
        }
        $this->type = $args["type"]??"all";
    }

    public function doRequest()
    {

        $e = new ExportSearch($this->search);


        return $e->toXml();
    }
}

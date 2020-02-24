<?php

namespace Anakeen\Fullsearch\Route;

use Anakeen\Core\DbManager;
use Anakeen\Fullsearch\SearchDomainDatabase;
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

        $data["config"] = array_values(SearchDomainManager::getConfig());

        foreach ($data["config"] as &$config) {
            $config["database"] = $this->getDbSize($config["name"]);

        }
        return $data;
    }

    protected function getDbSize($domainName)
    {
        $sql = <<<SQL
        SELECT 
        pg_total_relation_size('%s') AS "size",
        pg_size_pretty(pg_total_relation_size('%s')) AS "prettySize" ;
  
SQL;
        $db = new SearchDomainDatabase($domainName);

        $data= $db->getDbStats();
        $sql = sprintf($sql, pg_escape_string($db->getTableName()), pg_escape_string($db->getTableName()));

        DbManager::query($sql, $size, false, true);


        $data["size"] = [
            "prettySize" => $size["prettySize"],
           "size"=> intval($size["size"])];
        return $data;
    }
}
